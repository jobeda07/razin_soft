<?php

namespace App\Http\Controllers\Api;

use App\Models\Task;
use App\Models\User;
use App\Events\TestEvent;
use Illuminate\Http\Request;
use App\Events\TaskStatusUpdated;
use App\Http\Requests\TaskRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Resources\TaskResource;
use Illuminate\Support\Facades\Auth;
use App\Notifications\TaskStatusNotification;

class TaskAction extends Controller
{
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            $search = $request->query('search');
            //$query = Task::withTrashed()->orderByDesc('id'); //show with deleted data 
            $query = Task::orderByDesc('id');

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where("name", "like", "%{$search}%")
                        ->orWhere("status", "like", "%{$search}%")
                        ->orWhereHas('createdBy', function ($query) use ($search) {
                            $query->where("name", "like", "%{$search}%");
                        });
                });
            }

            $taskData = $query->where(function ($q) use ($user) {
                $q->where('created_by', $user->id)
                    ->orWhereJsonContains('assigned_user_ids', $user->id);
            })->paginate(50)->appends($request->query());

            return TaskResource::collection($taskData)
                ->additional(['status' => 200]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong: ' . $e->getMessage(),
                'status' => 500
            ]);
        }
    }
    public function store(TaskRequest $request)
    {

        try {
            DB::beginTransaction();
            $taskData = Task::create([
                'name' => ucfirst($request->name),
                'description' => $request->description,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'created_by' => Auth::user()->id,
                'assigned_user_ids' => [],
            ]);
            DB::commit();
            return response([
                'task-data' => new TaskResource($taskData),
                'message' => 'Data Created successfully',
                'status' => 201
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'error' => 'Something went wrong: ' . $e->getMessage(),
                'status' => 500
            ]);
        }
    }

    public function show($id)
    {
        try {
            $taskData = Task::find($id);
            if (!$taskData) {
                return response()->json([
                    'error' => 'data not found',
                    'status' => 500
                ]);
            }
            return response([
                'task-data' => new TaskResource($taskData),
                'status' => 200
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong: ' . $e->getMessage(),
                'status' => 500
            ]);
        }
    }

    public function update(TaskRequest $request, $id)
    {
        try {
            DB::beginTransaction();
            $user = Auth::user();
            $taskData = Task::where('id', $id)->where('created_by', $user->id)->first();
            if (! $taskData) {
                return response()->json([
                    'error' => 'data not found',
                    'status' => 500
                ]);
            }

            $taskData->update([
                'name' => ucfirst($request->name),
                'description' => $request->description,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'created_by' => $taskData->created_by,
                'assigned_user_ids' => $taskData->assigned_user_ids ?? [],
            ]);
            DB::commit();
            return response([
                'task-data' => new TaskResource($taskData),
                'message' => 'Data Update successfully',
                'status' => 201
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'error' => 'Something went wrong: ' . $e->getMessage(),
                'status' => 500
            ]);
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            $user = Auth::user();
            $taskData = Task::where('id', $id)->where('created_by', $user->id)->first();
            if (!$taskData) {
                return response()->json([
                    'error' => 'data not found',
                    'status' => 500
                ]);
            }
            $taskData->delete();
            // $taskData->forceDelete(); // for permanent delete
            DB::commit();
            return response([
                'message' => ' Data Delete successfully',
                'status' => 200
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'error' => 'Something went wrong: ' . $e->getMessage(),
                'status' => 500
            ]);
        }
    }

    public function user_list_for_assign()
    {
        try {
            $user = User::orderBy('id', 'desc')->get();
            $userData = [];
            foreach ($user as $item) {
                $userData[] = [
                    'id' => $item->id,
                    'name' => $item->name
                ];
            }
            return response()->json([
                'response_Data' => $userData,
                'status' => 200
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong: ' . $e->getMessage(),
                'status' => 500
            ]);
        }
    }

    public function task_assign(Request $request, $id)
    {
        $request->validate([
            'assigned_user_ids' => 'nullable|array',
            'assigned_user_ids.*' => 'exists:users,id',
        ]);

        try {
            DB::beginTransaction();
            $user = Auth::user();
            $taskData = Task::where('id', $id)->where('created_by', $user->id)->first();
            if (!$taskData) {
                return response()->json([
                    'error' => 'data not found',
                    'status' => 500
                ]);
            }

            $taskData->update([
                'assigned_user_ids' => $request->assigned_user_ids,
                'status' => 'assigned',
            ]);

            DB::commit();

            return response([
                'message' => ' Assign Task successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'error' => 'Something went wrong: ' . $e->getMessage(),
                'status' => 500
            ]);
        }
    }
    public function task_status(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:inProgress,completed,canceled',
        ]);

        try {
            DB::beginTransaction();
            $user = Auth::user();
            $taskData = Task::where('id', $id)
                ->where(function ($query) use ($user) {
                    $query->where('created_by', $user->id)
                        ->orWhereJsonContains('assigned_user_ids', $user->id);
                })
                ->first();
            if (!$taskData) {
                return response()->json([
                    'error' => 'data not found',
                    'status' => 500
                ]);
            }
            $taskData->update(['status' => $request->status]);

            $taskData->createdBy->notify(new TaskStatusNotification($taskData));

            TaskStatusUpdated::dispatch($taskData);

            DB::commit();

            return response([
                'task-data' => new TaskResource($taskData),
                'message' => ' Status Update successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'error' => 'Something went wrong: ' . $e->getMessage(),
                'status' => 500
            ]);
        }
    }

    public function task_summary()
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'error' => 'User not authenticated',
                    'status' => 401
                ]);
            }
            $statuses = ['pending', 'assigned', 'completed', 'inProgress', 'canceled'];
            $tasks = [];

            foreach ($statuses as $status) {
                $tasks["{$status}_task"] = Task::where('status', $status)
                    ->where(function ($query) use ($user) {
                        $query->where('created_by', $user->id)
                            ->orWhereJsonContains('assigned_user_ids', $user->id);
                    })->count();
            }

            return response()->json(array_merge($tasks, ['status' => 200]));
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong: ' . $e->getMessage(),
                'status' => 500
            ]);
        }
    }
}

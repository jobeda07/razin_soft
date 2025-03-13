<?php

namespace App\Events;

use App\Models\Task;
use Illuminate\Support\Facades\Log;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class TaskStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $task;

    public function __construct(Task $task)
    {
        $this->task = $task;
    }

    public function broadcastOn()
    {
        Log::info('Broadcasting TaskStatusUpdated Event liza', ['task' => $this->task->name, 'status' => $this->task->status]);

        return new Channel('task-status-channel');
    }

    public function broadcastAs()
    {

        return 'task.status.updated';
    }

    public function broadcastWith()
    {
        return [
            'message' => "Task '{$this->task->name}' status updated to '{$this->task->status}'!",
        ];
    }
}

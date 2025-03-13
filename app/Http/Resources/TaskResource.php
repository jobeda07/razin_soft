<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $assigned = User::whereIn('id', $this->assigned_user_ids)->get();
        return [
            'id' => $this->id,
            'name' => $this->name ?? '',
            'description' => $this->description ?? '',
            'start_date' => Carbon::parse($this->start_date)->format('d-m-Y'),
            'end_date' => Carbon::parse($this->end_date)->format('d-m-Y'),
            'status' => ucfirst($this->status),
            'assign_to' => $assigned->pluck('name')->implode(', '),
            'created_by' => $this->createdBy->name,
            'create_date_time' => $this->created_at->format('j F Y  g.i A'),
        ];
    }
}

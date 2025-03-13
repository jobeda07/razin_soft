<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;

class TaskStatusNotification extends Notification  implements ShouldQueue
{
    use Queueable;

    private $task;

    public function __construct($task)
    {
        $this->task = $task;
    }


    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    public function toArray($notifiable)
    {
        return [
            'message' => "Task '{$this->task->name}' status updated to '{$this->task->status}'!",
            'task_id' => $this->task->id,
            'status' => $this->task->status,
        ];
    }

    public function toBroadcast($notifiable)
    {
        Log::info('Broadcasting notification:', ['task_id' => $this->task->name, 'status' => $this->task->status,]);
        return new BroadcastMessage([
            'message' => "Task '{$this->task->name}' status updated to '{$this->task->status}'!",
            'task_id' => $this->task->id,
            'status' => $this->task->status,
        ]);
    }
}

<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class NewAdminCreatedNotification extends Notification
{
    use Queueable;

    public function __construct(public User $newAdmin)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Msimamizi Mpya Amejiunga!',
            'body' => "Mtumiaji {$this->newAdmin->name} amekamilisha usajili wa akaunti ya msimamizi.",
            'icon' => 'heroicon-o-user-plus',
        ];
    }
}

<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class NewWakalaCreatedNotification extends Notification
{
    use Queueable;

    // We accept the new Wakala user so we can use their name in the message
    public function __construct(public User $wakala)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database']; // This tells Laravel to save it to the database
    }

    // This defines the data that will be stored in the 'data' column
    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Wakala Mpya Amesajiliwa!',
            'body' => "Wakala '{$this->wakala->name}' ameongezwa kwenye mfumo.",
            'icon' => 'heroicon-o-user-group', // The icon for the notification
        ];
    }
}

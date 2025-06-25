<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\User; // The User model

class WakalaWelcomeNotification extends Notification
{
    use Queueable;

    // We can accept the user if we want to personalize the message, but for a generic
    // welcome, we don't need any constructor arguments.

    public function __construct()
    {
        //
    }

    public function via(object $notifiable): array
    {
        return ['database']; // It must be saved to the database
    }

    public function toDatabase(object $notifiable): array
    {
        // This is the data that will appear on the mobile app
        return [
            'title' => 'Karibu WakalaTel!',
            'body'  => "Hongera sana! Akaunti yako imefunguliwa na ipo tayari kutumika. Karibu kwenye timu!",
            'icon'  => 'heroicon-o-sparkles',
        ];
    }
}

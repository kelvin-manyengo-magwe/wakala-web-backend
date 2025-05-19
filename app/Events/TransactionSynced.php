<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Transaction;


class TransactionSynced
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $transaction;

     public function __construct(Transaction $transaction)
     {
         $this->transaction = $transaction->load(['customer', 'type', 'device', 'sms']);
     }

     public function broadcastOn()
     {
         return new Channel('transactions');
     }

     public function broadcastAs()
     {
         return 'TransactionSynced';
     }
}

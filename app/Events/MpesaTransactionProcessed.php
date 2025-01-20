<?php

namespace App\Events;

use App\Models\SubscriptionOrder;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class MpesaTransactionProcessed implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $order;
    public $success;

    public function __construct($order, $success)
    {
        $this->order = $order;
        $this->success = $success;
    }

    public function broadcastOn()
    {
        return new Channel('transaction.'. $this->order->payment_id);
    }

    public function broadcastAs()
    {
        return $this->success ? 'MpesaTransactionProcessed' : 'MpesaTransactionDeclined';
    }
}

?>
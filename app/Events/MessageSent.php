<?php
namespace App\Events;

// use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
class MessageSent implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;
    public $sender_id;
    public $reciever_id;
    public $message;

    public function __construct($sender_id,$reciever_id,$message)
    {
        $this->sender_id = $sender_id;
        $this->reciever_id = $reciever_id;
        $this->message = $message;
        \Log::info('MessageSent event triggered with message: ' . $this->message);
    }

    public function broadcastOn()
    {
        return new Channel('chat-channel');
    }
    public function broadcastAs()
    {
        return 'MessageSent';
    }
    public function broadcastWith()
    {
        return [
            'message' => [
                'sender_id' => $this->sender_id,
                'receiver_id' => $this->reciever_id,
                'content' => $this->message,
                'sender_name' => User::find($this->sender_id)->name, // Assuming you have a User model
            ]
        ];
    }
    public function shouldBroadcastNow()
    {
        return true;
    }
}


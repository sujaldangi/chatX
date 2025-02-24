<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Pusher\Pusher;

class PusherAuthController extends Controller
{
    public function authenticate(Request $request)
    {
        $pusher = new Pusher(
            config('broadcasting.connections.pusher.key'),
            config('broadcasting.connections.pusher.secret'),
            config('broadcasting.connections.pusher.app_id'),
            [
                'cluster' => config('broadcasting.connections.pusher.options.cluster'),
                'useTLS' => true,
            ]
        );

        $channelName = $request->input('channel_name');
        $socketId = $request->input('socket_id');

        // Validate if the user is allowed to access this channel
        $user = auth()->user();
        if ($user && $this->isUserAllowedToAccessChannel($channelName, $user)) {
            return response($pusher->socket_auth($channelName, $socketId));
        } else {
            return response('Forbidden', 403);
        }
    }

    private function isUserAllowedToAccessChannel($channelName, $user)
    {
        // Extract sender and receiver IDs from the channel name
        $parts = explode('.', $channelName);
        if (count($parts) === 3 && $parts[0] === 'chat') {
            $senderId = $parts[1];
            $receiverId = $parts[2];
            return in_array($user->id, [$senderId, $receiverId]);
        }
        return false;
    }
}
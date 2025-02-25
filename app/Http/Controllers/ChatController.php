<?php

namespace App\Http\Controllers;

use App\Services\FirebaseService;
// use App\Services\FirebaseNotificationService;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Events\MessageSent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Pusher\Pusher;

class ChatController extends Controller
{
    protected $firebaseService;
    // protected $firebaseNotificationService;

    public function __construct(FirebaseService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
        // $this->firebaseNotificationService = $firebaseNotificationService;
    }

    public function startChat(Request $request)
    {
    
        $rules = [
            'receiver_id' => 'required|integer',
            'sender_id' => 'required|integer',
        ];
    
        $validator = Validator::make($request->all(), $rules);
    
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 400);
        }
    
    
        try {
            // Call FirebaseService
            $this->firebaseService->startChat($request->sender_id, $request->receiver_id);
    
    
            // Return a successful JSON response
            return response()->json(['message' => 'Chat started successfully'], 200);
        } catch (\Exception $e) {
            \Log::error('Error starting chat: ' . $e->getMessage());
    
            // Return a failed JSON response with an error message
            return response()->json([
                'message' => 'Error starting chat',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    

    // public function startGroupChat(Request $request)
    // {
    //     $rules = [
    //         'group_name' => 'required|string',
    //         'created_by' => 'required|string',
    //         'participants' => 'required|array',
    //     ];

    //     $validator = Validator::make($request->all(), $rules);
    //     $validated = $validator->validated();
       
    //     if ($validator->fails()) {
    //         return $this->sendError($validator->errors(),422);
    //     }
    //     $validated['participants'][] =  $validated['created_by'];
      

    //     return $this->firebaseService->startGroupChat(
    //         $validated['group_name'],
    //         $validated['created_by'],
    //         $validated['participants'],
    //     );
       
    // }

    // Store a new message in Firebase
    public function sendMessage(Request $request)
    {
        \Log::info('in sendMessage');
        $rules = [
            'sender_id' => 'required|string',
            'message' => 'required|string',
            'chat_type' => 'required|string', // 'individual' or 'group'
            'receiver_id' => 'nullable|integer', // Only required for individual chat
            'group_id' => 'nullable|string',  // Only required for group chat
        ];

        $validator = Validator::make($request->all(), $rules);
        $validated = $validator->validated();
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }
        \Log::info('validation cleared');

        if ($validated['chat_type'] == 'individual') {
            $response = $this->firebaseService->storeMessage(
                $validated['sender_id'],
                $validated['receiver_id'],
                $validated['message']
            );
           
            $pusher = new Pusher(env('PUSHER_APP_KEY'), env('PUSHER_APP_SECRET'), env('PUSHER_APP_ID'), [
                'cluster' => env('PUSHER_APP_CLUSTER'),
                'useTLS' => true,
            ]);
    
            $pusher->trigger('chat-list.' . $validated['sender_id'], 'MessageSent', [
                'sender_id' => $validated['sender_id'],
                'receiver_id' => $validated['receiver_id'],
                'message' => $validated['message'],
            ]);
    
            $pusher->trigger('chat-list.' . $validated['receiver_id'], 'MessageSent', [
                'sender_id' => $validated['sender_id'],
                'receiver_id' => $validated['receiver_id'],
                'message' => $validated['message'],
            ]);
            \Log::info('Broadcasting event with message: ' . $validated['message']);
            $a = event(new MessageSent($validated['sender_id'],$validated['receiver_id'],$validated['message'],
            ));
            \Log::info('Event broadcast result: ', ['result' => $a]);


            return $response;
        } elseif ($validated['chat_type'] == 'group') {
            $response = $this->firebaseService->storeGroupMessage(
                $validated['sender_id'],
                $validated['message'],
                $validated['group_id'],
            );
          
            

            return $response;
        }
    }

    public function getChats(Request $request)
    {
        $userId = $request->user_id; // Assuming user_id is passed in the request
    
        // Validate that the user_id is provided
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|string',
        ]);
        
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), 422);
        }
    
        // Fetch chats that involve the given user
        $chats = $this->firebaseService->getChatsForUser($userId);
    
        if ($chats === null) {
            return $this->sendResponse([],'No chats found');
        }
        return response()->json(['chats' => $chats], 200);
    }

    // public function sendNotification($token,$title,$body)
    // {
        
    //     return $this->firebaseNotificationService->sendNotification(
    //         $token,
    //         $title,
    //         $body
    //     );
    // }

    public function fetchUsers()
    {
        $loggedInUserId = Auth::id();
        $users = User::where('id', '!=', $loggedInUserId)
                 ->get(['id', 'first_name', 'last_name', 'picture']);

        return response()->json(['users' => $users]);
    }

    public function getChatMessages(Request $request)
    {
        $userId = $request->input('userId');    
        $receiverId = $request->input('receiverId');
        $chats = $this->firebaseService->getMessagesForChat($userId,$receiverId);
        if ($chats === null) {
            return response()->json([],'No chats found');
        }
        return response()->json(['chats' => $chats], 200);
    }


    


}

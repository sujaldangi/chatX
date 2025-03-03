<?php

namespace App\Http\Controllers;

use App\Services\FirebaseService; // Import the Firebase service for interacting with Firebase
// use App\Services\FirebaseNotificationService; // Firebase notification service (commented out)
// Import necessary Laravel classes
use Illuminate\Support\Facades\Validator; // Validator for input validation
use App\Models\User; // User model to interact with the users table
use App\Events\MessageSent; // Event triggered when a message is sent
use Illuminate\Support\Facades\Auth; // For accessing the authenticated user
use Illuminate\Http\Request; // Request class to handle incoming HTTP requests
use Pusher\Pusher; // Pusher library for real-time notifications

class ChatController extends Controller
{
    protected $firebaseService; // Declare a property for the Firebase service
    // protected $firebaseNotificationService; // Declare a property for the Firebase notification service (commented out)

    // Constructor function to initialize the Firebase service (and Firebase Notification service if needed)
    public function __construct(FirebaseService $firebaseService)
    {
        $this->firebaseService = $firebaseService; // Inject FirebaseService into the controller
        // $this->firebaseNotificationService = $firebaseNotificationService; // Inject FirebaseNotificationService (commented out)
    }

    // Method to start a new chat
    public function startChat(Request $request)
    {
        // Validation rules for the input fields
        $rules = [
            'receiver_id' => 'required|integer', // receiver_id must be an integer and is required
            'sender_id' => 'required|integer',   // sender_id must be an integer and is required
        ];

        // Validate the request data
        $validator = Validator::make($request->all(), $rules);

        // If validation fails, return an error response
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed', // Message for failed validation
                'errors' => $validator->errors()  // Return the validation errors
            ], 400); // Return 400 Bad Request
        }

        try {
            // Call the Firebase service to start a chat between the sender and receiver
            $this->firebaseService->startChat($request->sender_id, $request->receiver_id);

            // Return a successful response indicating the chat was started
            return response()->json(['message' => 'Chat started successfully'], 200);
        } catch (\Exception $e) {
            // If an error occurs, log it and return an error response
            \Log::error('Error starting chat: ' . $e->getMessage());

            return response()->json([
                'message' => 'Error starting chat', // Error message
                'error' => $e->getMessage()         // The exception message
            ], 500); // Return 500 Internal Server Error
        }
    }

    // Method to send a message
    public function sendMessage(Request $request)
    {
        // Validation rules for sending a message
        $rules = [
            'sender_id' => 'required|string',   // sender_id must be a string and is required
            'message' => 'required|string',     // message content must be a string and is required
            'chat_type' => 'required|string',   // chat_type must be a string ('individual' or 'group')
            'receiver_id' => 'nullable|integer', // receiver_id is required for individual chat
            'group_id' => 'nullable|string',    // group_id is required for group chat
        ];

        // Validate the request data
        $validator = Validator::make($request->all(), $rules);
        $validated = $validator->validated(); // Get validated data

        // If validation fails, return an error response
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422); // Return 422 Unprocessable Entity
        }

        // Check if the chat type is 'individual'
        if ($validated['chat_type'] == 'individual') {
            // Call the Firebase service to store the individual message
            $response = $this->firebaseService->storeMessage(
                $validated['sender_id'],   // Sender ID
                $validated['receiver_id'], // Receiver ID
                $validated['message']      // Message content
            );

            // Initialize Pusher for real-time notifications
            $pusher = new Pusher(env('PUSHER_APP_KEY'), env('PUSHER_APP_SECRET'), env('PUSHER_APP_ID'), [
                'cluster' => env('PUSHER_APP_CLUSTER'),
                'useTLS' => true, // Use secure TLS connection
            ]);

            // Trigger message sent event for the sender's chat list
            $pusher->trigger('chat-list.' . $validated['sender_id'], 'MessageSent', [
                'sender_id' => $validated['sender_id'],
                'receiver_id' => $validated['receiver_id'],
                'message' => $validated['message'],
            ]);

            // Trigger message sent event for the receiver's chat list
            $pusher->trigger('chat-list.' . $validated['receiver_id'], 'MessageSent', [
                'sender_id' => $validated['sender_id'],
                'receiver_id' => $validated['receiver_id'],
                'message' => $validated['message'],
            ]);

            // Trigger the MessageSent event to notify about the new message
            event(new MessageSent(
                $validated['sender_id'],
                $validated['receiver_id'],
                $validated['message'],
            ));

            // Return the response from Firebase service
            return $response;
        } elseif ($validated['chat_type'] == 'group') {
            // For group chat, store the group message in Firebase
            $response = $this->firebaseService->storeGroupMessage(
                $validated['sender_id'],   // Sender ID
                $validated['message'],     // Message content
                $validated['group_id'],    // Group ID
            );

            // Return the response from Firebase service
            return $response;
        }
    }

    // Method to fetch all chats for a given user
    public function getChats(Request $request)
    {
        $userId = $request->user_id; // Get user_id from the request

        // Validate the user_id parameter
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|string', // user_id is required
        ]);

        // If validation fails, return an error response
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), 422); // Return 422 Unprocessable Entity
        }

        // Fetch chats for the user from Firebase
        $chats = $this->firebaseService->getChatsForUser($userId);

        // If no chats are found, return an empty response
        if ($chats === null) {
            return $this->sendResponse([], 'No chats found');
        }

        // Return the chats as a JSON response
        return response()->json(['chats' => $chats], 200); // Return 200 OK
    }

    // Method to fetch all users excluding the logged-in user
    public function fetchUsers()
    {
        $loggedInUserId = Auth::id(); // Get the logged-in user's ID
        // Fetch all users excluding the logged-in user
        $users = User::where('id', '!=', $loggedInUserId)
            ->get(['id', 'first_name', 'last_name', 'picture']); // Fetch user ID, name, and picture

        // Return the users as a JSON response
        return response()->json(['users' => $users]);
    }

    // Method to fetch messages for a specific chat
    public function getChatMessages(Request $request)
    {
        $userId = $request->input('userId'); // Get userId from the request
        $receiverId = $request->input('receiverId'); // Get receiverId from the request
        // Fetch messages between the user and receiver from Firebase
        $chats = $this->firebaseService->getMessagesForChat($userId, $receiverId);

        // If no chats are found, return an empty response
        if ($chats === null) {
            return response()->json([], 'No chats found');
        }

        // Return the chats as a JSON response
        return response()->json(['chats' => $chats], 200); // Return 200 OK
    }
}

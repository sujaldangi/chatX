<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Carbon\Carbon;
use App\Models\User;

class FirebaseService
{
    protected $database;
    protected $auth;

    public function __construct()
    {
        // Initialize Firebase with the service account and database URI
        $factory = (new Factory)
            ->withServiceAccount('/home/ashok/Downloads/lumenapi-11b39-firebase-adminsdk-fbsvc-efca10dc28.json') // Path to the service account JSON file
            ->withDatabaseUri('https://lumenapi-11b39-default-rtdb.asia-southeast1.firebasedatabase.app/'); // Firebase Realtime Database URI

        // Create instances of database and auth services
        $this->database = $factory->createDatabase();
        $this->auth = $factory->createAuth();
    }

    // Start a chat between two users (sender and receiver)
    public function startChat($senderId, $receiverId)
    {
        // Generate a unique key for the chat based on sender and receiver IDs
        $customKey = $senderId < $receiverId ? $senderId . $receiverId : $receiverId . $senderId;

        // Check if a chat already exists
        $existingChat = $this->database->getReference('chat/' . $customKey)->getValue();
        if ($existingChat) {
            // If chat already exists, return a response with an error message
            $response = ['success' => false, 'message' => 'Chat already exists'];
            return response()->json($response, 400);
        }

        // Otherwise, start a new individual chat with the participants
        $participantsData = [
            'participants' => [
                '0' => $senderId,
                '1' => $receiverId,
            ],
            'type' => 'individual'
        ];

        // Store the chat data in Firebase
        $this->database->getReference('chat/' . $customKey)
            ->set($participantsData);

        // Return success response
        $response = ['success' => true, 'data' => [], 'message' => 'chat started say hi!'];
        return response()->json($response, 200);
    }

    // Start a group chat with a specified group name, creator, and participants
    public function startGroupChat($groupName, $createdBy, $participants)
    {
        // Generate a unique ID for the group chat
        $uniqueGroupId = uniqid();

        // Prepare the group chat data
        $participantsData = [
            'group_name' => $groupName,
            'participants' => $participants,
            'type' => 'group',
            'created_by' => $createdBy,
        ];

        // Store the group chat data in Firebase
        $this->database->getReference('chat/' . $uniqueGroupId)
            ->set($participantsData);

        // Return success response with group ID
        $response = ['success' => true, 'data' => ['group_id' => $uniqueGroupId], 'message' => 'Group created'];
        return response()->json($response, 200);
    }

    // Store a message in Firebase Realtime Database (for individual chat)
    public function storeMessage($senderId, $receiverId, $message)
    {
        // Generate the chat key (similar to startChat method)
        $chatKey = $senderId < $receiverId ? $senderId . $receiverId : $receiverId . $senderId;
        $chatRef = $this->database->getReference('chat/' . $chatKey);

        // Retrieve the chat data to check if it exists
        $chatData = $chatRef->getSnapshot()->getValue();
        if ($chatData === null) {
            // If no chat data exists, start a new chat
            $this->startChat($senderId, $receiverId);
        }

        // Prepare the message data with sender ID, content, and timestamp
        $messageData = [
            'sender_id' => $senderId,
            'content' => $message,
            'timestamp' => Carbon::now()->toDateTimeString(), // Use Carbon to get the current timestamp
        ];

        // Push the new message to the Firebase Realtime Database
        $this->database->getReference('chat/' . $chatKey . '/messages')
            ->push($messageData);

        // Return success response
        $response = ['success' => true, 'data' => [], 'message' => 'message sent'];
        return response()->json($response, 200);
    }

    // Store a message in a group chat
    public function storeGroupMessage($senderId, $message, $groupName)
    {
        // Reference to the group chat in Firebase
        $groupRef = $this->database->getReference('chat/' . $groupName);

        // Retrieve group chat data
        $groupData = $groupRef->getSnapshot()->getValue();
        if ($groupData === null) {
            // If the group doesn't exist, return an error
            return $this->error('Group not found', 403);
        }

        // Check if the sender is part of the group
        if (!in_array($senderId, $groupData['participants'])) {
            // If sender is not part of the group, return an error
            return $this->error('Sender is not a participant', 403);
        }

        // Prepare the group message data
        $messageData = [
            'sender_id' => $senderId,
            'content' => $message,
            'timestamp' => Carbon::now()->toDateTimeString(),
        ];

        // Push the message to the group chat in Firebase
        $this->database->getReference('chat/' . $groupName . '/messages')
            ->push($messageData);

        // Return success response
        $response = ['success' => true, 'data' => [], 'message' => 'message sent'];
        return response()->json($response, 200);
    }

    // Retrieve chats that involve a specific user
    public function getChatsForUser($userId)
    {
        // Reference to all the chats in Firebase
        $chatsRef = $this->database->getReference('chat');

        // Retrieve all chats
        $chatsData = $chatsRef->getSnapshot()->getValue();

        // If no chats are found, return null
        if ($chatsData === null) {
            return null;
        }

        // Initialize an array to store user chats
        $userChats = [];

        // Loop through all chats and check if the user is a participant
        foreach ($chatsData as $chatKey => $chatData) {
            // Ensure the 'participants' key exists and the user is part of the chat
            if (isset($chatData['participants']) && in_array($userId, $chatData['participants'])) {
                // Get the last message of the chat (or default to 'No messages yet' if no messages)
                $lastMessage = 'No messages yet';  
                if (isset($chatData['messages']) && !empty($chatData['messages'])) {
                    $lastMessage = end($chatData['messages']);
                    $lastMessage = $lastMessage['content'] ?? 'No content'; 
                }

                // Get the names of the participants
                $p1name = User::find($chatData['participants'][1])->first_name ?? 'Unknown';
                $p2name = User::find($chatData['participants'][0])->first_name ?? 'Unknown';

                // Build the chat data for this chat
                $userChats[] = [
                    'chat_key' => $chatKey,
                    'name_p1' => $p1name,
                    'name_p2' => $p2name,
                    'last_message' => $lastMessage,
                    'participants' => $chatData['participants'],
                    'group_name' => $chatData['group_name'] ?? null,
                    'messages' => $chatData['messages'] ?? null,
                    'type' => $chatData['type'] ?? null,
                    'created_by' => $chatData['created_by'] ?? null, 
                ];
            }
        }

        return $userChats;
    }

    // Retrieve messages for a specific chat (individual chat with sender and receiver)
    public function getMessagesForChat($userId, $receiverId)
    {
        // Generate the chat key based on user and receiver IDs
        $chatKey = $userId < $receiverId ? $userId . $receiverId : $receiverId . $userId;

        // Reference to the chat in Firebase
        $chatRef = $this->database->getReference('chat/' . $chatKey);
        $chatData = $chatRef->getSnapshot()->getValue();

        // If no chat is found, return a 404 response
        if ($chatData === null) {
            $response = [
                'success' => false,
                'message' => 'Chat not found',
            ];
            return response()->json($response, 404);
        }

        // Check if the participants key exists
        if (!isset($chatData['participants'])) {
            \Log::error("Participants key missing for chat: " . $chatKey);
            $response = [
                'success' => false,
                'message' => 'Participants data is missing',
            ];
            return response()->json($response, 400);
        }

        // Retrieve the receiver's user data
        $user = User::find($receiverId);
        $user = $user->toArray();

        // Return chat and user details
        return [
            'participants' => $chatData['participants'],
            'messages' => $chatData['messages'] ?? [],
            'user_name_chat' => $user['first_name'] . ' ' . $user['last_name'],
            'picture' => $user['picture'],
        ];
    }
}

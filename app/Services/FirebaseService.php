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
        // Initialize Firebase
        $factory = (new Factory)
            ->withServiceAccount('/home/ashok/Downloads/lumenapi-11b39-firebase-adminsdk-fbsvc-efca10dc28.json') 
            ->withDatabaseUri('https://lumenapi-11b39-default-rtdb.asia-southeast1.firebasedatabase.app/'); 

      
        $this->database = $factory->createDatabase();
        $this->auth = $factory->createAuth();
        
    }

    public function startChat($senderId, $receiverId)
    {
        $participantsData = [
            'participants' => [
                '0' => $senderId,
                '1' => $receiverId,
            ],
            'type' => 'individual',
        ];
      
        $customKey = $senderId.$receiverId;
        $this->database->getReference('chat/' . $customKey)
        ->set($participantsData);
        $response = ['success' => true, 'data' => [], 'message' => 'chat started say hi!'];
        
        return response()->json($response, 200);
        
       

    }

    public function startGroupChat($groupName, $createdBy, $participants)
    {
        $uniqueGroupId = uniqid();
        $participantsData = [
            'group_name' => $groupName,
            'participants' => $participants,
            'type' => 'group',
            'created_by' => $createdBy,
        ];
        // $participantsData = [
        //     'participants' => [],
        //     'type' => 'group',
        //     'created_by' => $createdBy,
        // ];
        
        
        // foreach ($participants as $key => $participant) {
        //     $participantsData['participants'][$key] = $participant;
        // }
      
        // $customKey = $groupName;
        $this->database->getReference('chat/' . $uniqueGroupId)
        ->set($participantsData);
       
        $response = ['success' => true, 'data' => ['group_id' => $uniqueGroupId], 'message' => 'Group created'];
        // dd($response);
        return response()->json($response, 200);
        
       

    }

    // Store message in Firebase Realtime Database
    public function storeMessage($senderId, $receiverId,$message)
    {
        
        $chatKey = $senderId < $receiverId ? $senderId . $receiverId : $receiverId . $senderId;
        $chatRef = $this->database->getReference('chat/' . $chatKey);
        $chatData = $chatRef->getSnapshot()->getValue();
        if ($chatData === null) {
            $this->startChat($senderId, $receiverId);
        }
        $messageData = [
            'sender_id' => $senderId,
            'content' => $message,
            'timestamp' => Carbon::now()->toDateTimeString(),
        ];
        $this->database->getReference('chat/' . $chatKey . '/messages')
        ->push($messageData);
        $response = ['success' => true, 'data' => [], 'message' => 'message sent'];
        
        return response()->json($response, 200);
       
    }

    public function storeGroupMessage($senderId, $message, $groupName)
    {
        $groupRef = $this->database->getReference('chat/' . $groupName);
        $groupData = $groupRef->getSnapshot()->getValue();
        if ($groupData === null) {
            return $this->error('Group not found',403);
        }

        if (!in_array($senderId, $groupData['participants'])) {
            return $this->error('Sender is not a participant',403);
        }
        $messageData = [
            'sender_id' => $senderId,
            'content' => $message,
            'timestamp' => Carbon::now()->toDateTimeString(),
        ];

        $this->database->getReference('chat/'.$groupName. '/messages')
            ->push($messageData);

        $response = ['success' => true, 'data' => [], 'message' => 'message sent'];
        
        return response()->json($response, 200);
        
    }

    public function getChatsForUser($userId)
    {
        // Reference to the chats in Firebase
        $chatsRef = $this->database->getReference('chat');
        
        // Retrieve all chats
        $chatsData = $chatsRef->getSnapshot()->getValue();
        \Log::info($chatsData);
        // If no chats are found, return null
        if ($chatsData === null) {
            return null;
        }

        $userChats = [];
        
        // Loop through each chat to check if the user is a participant
        foreach ($chatsData as $chatKey => $chatData) {
            if (in_array($userId, $chatData['participants'])) {
                // Get the last message if messages exist, otherwise default to 'No messages yet'
                $lastMessage = 'No messages yet';  // Default message
                if (isset($chatData['messages']) && !empty($chatData['messages'])) {
                    $lastMessage = end($chatData['messages']);
                    $lastMessage = $lastMessage['content'] ?? 'No content';  // Get the content if available
                }

                // Get the user's name (you may want to handle this depending on chat type)
                $p1name = User::find($chatData['participants'][1])->first_name;
                $p2name = User::find($chatData['participants'][0])->first_name;
                // Build chat data
                $userChats[] = [
                    'chat_key' => $chatKey,
                    'name_p1' => $p1name,
                    'name_p2' => $p2name,
                    'last_message' => $lastMessage,
                    'participants' => $chatData['participants'],
                    'group_name' => $chatData['group_name'] ?? null,
                    'messages' => $chatData['messages'] ?? null,
                    'type' => $chatData['type'] ?? null,
                    'created_by' => $chatData['created_by'] ?? null, // For group chats
                ];
            }
        }

        return $userChats;
    }

    public function getMessagesForChat($userId,$recieverId)
{
    $chatKey = $userId < $recieverId ? $userId . $recieverId : $recieverId . $userId;
  
    $chatRef = $this->database->getReference('chat/' . $chatKey);
    $chatData = $chatRef->getSnapshot()->getValue();
    
    // If no chats are found, return null
    if ($chatData === null) {
        return null;
    }
    \Log::info($chatData);
   
    
    return [
        'participants' => $chatData['participants'],
        'messages' => $chatData['messages'] ?? [],
    ];
    
}



   
}

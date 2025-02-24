<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://js.pusher.com/7.0/pusher.min.js"></script>
    <title>Dashboard</title>
    <style>
       /* Basic Reset */
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            padding: 0;
        }

        /* Header Styling (Logout Button) */
        header {
            background-color: #333;
            color: white;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: fixed;
            width: 98%;
            top: 0;
            left: 0;
            z-index: 1000; /* Keep the header on top */
        }

        header h1 {
            margin: 0;
        }

        /* Sidebar Styling */
        #sidebar {
            width: 250px;
            position: fixed;
            top: 60px; /* Adjusted to avoid overlapping header */
            left: 0;
            bottom: 0;
            background-color: #f4f4f4;
            padding: 20px;
            overflow-y: auto;
            border-right: 1px solid #ccc;
        }

        /* Prevent Sidebar from overlapping header */
        #sidebar button {
            width: 100%;
            padding: 10px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            margin-bottom: 15px;
            cursor: pointer;
        }

        #sidebar button:hover {
            background-color: #218838;
        }

        /* Chat Box Positioning */
        #chat-box {
            position: fixed;
            top: 60px;
            left: 292px;
            right: 0;
            bottom: 0;
            background-color: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            display: flex;
            flex-direction: column;
            z-index: 999;
            overflow: hidden;
        }

        /* Container for the messages */
        #messages {
            flex-grow: 1;
            overflow-y: auto;
            margin-bottom: 10px;
            padding-right: 10px; /* Add space for scrollbar */
        }

        /* Positioning the message input and button at the bottom */
        #message-input {
            padding: 10px;
            margin-top: 10px;
            border: 1px solid #ccc;
            border-radius: 8px;
            width: 100%;
            box-sizing: border-box;  /* Ensure padding doesn't affect width */
            position: sticky;
            bottom: 0;
            background-color: white;
        }

        #chat-box button {
            padding: 10px;
            background-color: #007bff;
            color: blue;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 10px;
            position: sticky;
            bottom: 0;
            background-color: white;
        }

        #chat-box button:hover {
            background-color: #0056b3;
        }



        /* Modal for User List */
        #userModal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 2000;  /* Ensures the modal is on top */
        }

        /* Ensure the modal content is properly styled */
        .modal-content {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            width: 250px;
            max-height: 80%;
            overflow-y: auto;
            z-index: 2001;  /* Content on top of the overlay */
        }

        /* Close button in modal */
        .modal-content span {
            cursor: pointer;
            color: red;
            font-size: 20px;
        }

        /* Chat Message Styling */
        .message {
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 8px;
            max-width: 70%;
            word-wrap: break-word;
        }

        .message.sent {
            background-color: #007bff;
            color: white;
            align-self: flex-end;
        }

        .message.received {
            background-color: #f1f1f1;
            color: black;
            align-self: flex-start;
        }

        #messages {
            display: flex;
            flex-direction: column;
            gap: 10px;
            justify-content: flex-start;
            padding: 10px;
        }
        /* Fix image size in the modal */
        #userModal .modal-content img {
            width: 50px;           /* Set a fixed width for the image */
            height: 50px;          /* Set a fixed height for the image */
            object-fit: cover;     /* Ensures that the image covers the area without stretching */
            border-radius: 50%;    /* Makes the image circular */
            margin-right: 10px;    /* Space between image and text */
            flex-shrink: 0;        /* Prevents the image from shrinking */
        }

        /* Ensure the user list is properly aligned */
        #userModal .modal-content .user {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            cursor: pointer;
        }

        #userModal .modal-content .user span {
            font-size: 16px;
            margin-left: 10px;
        }
        .message.sent {
            background-color: #007bff; /* Blue for sent messages */
            color: white;
            align-self: flex-end;
        }

        .message.received {
            background-color: #f1f1f1; /* Grey for received messages */
            color: black;
            align-self: flex-start;
        }




    </style>
</head>
<body>

<header>
    <h1>Welcome to Dashboard</h1>
    <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
        @csrf
        <button type="submit" style="padding: 8px 16px; background-color: #dc3545; color: white; border: none; border-radius: 4px;">Logout</button>
    </form>
</header>

<!-- Sidebar for Users and Chat List -->
<div id="sidebar">
    <button id="new-chat-btn" onclick="openUserModal()">New Chat</button>
    <div id="user-list"></div>

    <!-- Chat List below New Chat button -->
    <div id="chat-list"></div>
</div>

<!-- Modal for User List -->
<div id="userModal">
    <div class="modal-content">
        <span onclick="closeUserModal()">&times;</span>
        <h2>Select a User</h2>
        <div id="user-list-modal"></div>
    </div>
</div>

<!-- Chat Box (Visible after selecting a chat) -->
<div id="chat-box">
    <div id="messages"></div>
    <input type="text" id="message-input" placeholder="Type a message..." />
    <button onclick="sendMessage()">Send</button>
</div>

<script>
     Pusher.logToConsole = true;
        var pusher = new Pusher('24c0536b2bb45e29a90e', {
            cluster: 'ap2'
        });

        // Subscribe to the chat channel
        var channel = pusher.subscribe('chat-channel');


        channel.bind('MessageSent', function (data) {
    console.log('Message received via Pusher:', data);
    const message = data.message;
    
    // Create a new message element
    const messageElement = document.createElement('div');
    
    // Check if the message is from the current user or the other user
    if (message.sender_id === "{{ auth()->user()->id }}") {
        messageElement.classList.add('message', 'sent'); // Add 'sent' class for current user's messages
    } else {
        messageElement.classList.add('message', 'received'); // Add 'received' class for other user's messages
    }
    
    messageElement.innerHTML = `
        <p><strong>${message.sender_id === "{{ auth()->user()->id }}" ? "You" : message.sender_name}:</strong> ${message.content}</p>
    `;

    // Append the new message to the messages container
    document.getElementById('messages').appendChild(messageElement);
});
    document.addEventListener("DOMContentLoaded", function () {
        fetchChats();  // Fetch chats on page load
    });

    // Open Modal to select a user
    function openUserModal() {
        document.getElementById("userModal").style.display = "block";
        fetchUsers();
    }

    // Close the user modal
    function closeUserModal() {
        document.getElementById("userModal").style.display = "none";
    }

    // Fetch Users from Server to show in the modal
    function fetchUsers() {
        fetch('{{ route('fetch.users') }}')
            .then(response => response.json())
            .then(data => {
                const userListContainer = document.getElementById("user-list-modal");
                userListContainer.innerHTML = "";  // Clear the list before appending new data

                data.users.forEach(user => {
                    const userDiv = document.createElement("div");
                    userDiv.classList.add("user");
                    const imageUrl = user.picture ? `/storage/${user.picture}` : '/storage/pictures/default.webp';
                    userDiv.innerHTML = `
                        <img src="${imageUrl}" alt="User"/>
                        <span>${user.first_name} ${user.last_name}</span>
                    `;
                    userDiv.addEventListener("click", () => startChat(user.id));
                    userListContainer.appendChild(userDiv);
                });
            })
            .catch(error => console.log('Error fetching users:', error));
    }

    // Start a chat with a selected user
    function startChat(receiverId) {
        fetch('{{ route('start.chat') }}', {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                sender_id: "{{ auth()->user()->id }}",
                receiver_id: receiverId
            })
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message);  // Show success message
            closeUserModal();     // Close the modal
            fetchChats();         // Refresh chat list after starting a new chat
        })
        .catch(error => console.log("Error starting chat:", error));
    }

    // Fetch Chats for the logged-in user
    function fetchChats() {
        fetch('{{ route('get.chats') }}', {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                user_id: "{{ auth()->user()->id }}"
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.chats.length > 0) {
                displayChats(data.chats);
            } else {
                document.getElementById("chat-list").innerHTML = "<p>No chats available.</p>";
            }
        })
        .catch(error => console.log("Error fetching chats:", error));
    }

    // Display Chats in the sidebar
    function displayChats(chats) {
        const chatListContainer = document.getElementById("chat-list");
        chatListContainer.innerHTML = "";  // Clear the existing chats
        const currentUserName = "{{ auth()->user()->first_name }}";
        console.log(currentUserName);
        chats.forEach(chat => {
            const chatDiv = document.createElement("div");
            chatDiv.classList.add("chat");
            let chatName = chat.name_p1;  
            if (currentUserName === chat.name_p1) {
                chatName = chat.name_p2; 
            } else if (currentUserName === chat.name_p2) {
                chatName = chat.name_p1;
            }
            const receiverId = chat.participants[0] == "{{ auth()->user()->id }}" ? chat.participants[1] : chat.participants[0];
            chatDiv.innerHTML = `
                <p><strong>Chat with:</strong> ${chatName}</p>
                <p><strong>Last Message:</strong> ${chat.last_message}</p>
                <button onclick="openChat(${chat.id}, ${receiverId})">Open Chat</button>
            `;
            chatListContainer.appendChild(chatDiv);
        });
    }

  

    function openChat(chatId, receiverId) {
    window.currentReceiverId = receiverId;
    document.getElementById("chat-box").style.display = "block";
    
    const userId = "{{ auth()->user()->id }}"; // Get current user ID from Blade template

    // Fetch messages for the selected chat using userId and receiverId as URL parameters
    fetch(`/get-chat-messages`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
                userId: "{{ auth()->user()->id }}",
                receiverId: receiverId
            })
    })
    .then(response => response.json())
    .then(data => {
        // Display messages in the chat box
        console.log(data.chats);
        displayMessages(data.chats);
        const messagesContainer = document.getElementById("messages");
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    })
    .catch(error => console.log("Error fetching messages:", error));
}


    // Send a message
    function sendMessage() {
        const messageInput = document.getElementById('message-input');
        const message = messageInput.value;
        const senderId = "{{ auth()->user()->id }}"; 
        const receiverId = window.currentReceiverId; // Replace with dynamic receiver ID

        messageInput.value = '';

        fetch('{{ route('send.message') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                sender_id: senderId,
                message: message,
                chat_type: 'individual',
                receiver_id: receiverId,
            })
        })
        .then(response => response.json())
        .then(data => {
            console.log('Message sent:', data);
        })
        .catch(error => console.error('Error sending message:', error));
    }
    // Display Messages in the chat box
    function displayMessages(chats) {
    const messagesContainer = document.getElementById("messages");
    messagesContainer.innerHTML = "";  // Clear previous messages

    // Check if messages exist in the chat object
    const messages = chats.messages || {}; // Use empty object if no messages found

    if (Object.keys(messages).length === 0) {
        messagesContainer.innerHTML = "<p>No messages yet.</p>";
    } else {
        // Loop through the message objects (use Object.entries to get key-value pairs)
        Object.entries(messages).forEach(([key, message]) => {
            const messageDiv = document.createElement("div");
            messageDiv.classList.add("message");

            // Check if the message is from the current user or the other participant
            if (message.sender_id === "{{ auth()->user()->id }}") {
                messageDiv.classList.add("sent");  // Current user's message
            } else {
                messageDiv.classList.add("received");  // Other user's message
            }

            messageDiv.innerHTML = `
                <p><strong>${message.sender_id === "{{ auth()->user()->id }}" ? "You" : "User"}:</strong> ${message.content}</p>
            `;
            messagesContainer.appendChild(messageDiv);
        });
    }
}


//     function displayMessages(chats) {
//     const messagesContainer = document.getElementById("messages");
//     messagesContainer.innerHTML = "";  // Clear previous messages

//     // // Check if the receiver is part of the participants
//     // if (!chats.participants.includes(window.currentReceiverId)) {
//     //     messagesContainer.innerHTML = "<p>No chat found with the selected user.</p>";
//     //     return;
//     // }

//     const messages = chats.messages || {}; // Use empty object if no messages found
    
//     if (Object.keys(messages).length === 0) {
//         messagesContainer.innerHTML = "<p>No messages yet.</p>";
//     } else {
//         // Loop through the message objects (use Object.entries to get key-value pairs)
//         Object.entries(messages).forEach(([key, message]) => {
//             const messageDiv = document.createElement("div");
//             messageDiv.classList.add("message");
//             messageDiv.innerHTML = `
//                 <p><strong>${message.sender_id}:</strong> ${message.content}</p>
//             `;
//             messagesContainer.appendChild(messageDiv);
//         });
//     }
// }


</script>

</body>
</html>

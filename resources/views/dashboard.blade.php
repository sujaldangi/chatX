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
            background-color: #333;
        }

        /* Header Styling (Logout Button) */
        header {
            background-color: #000;
            color: white;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: fixed;
            width: 98%;
            top: 0;
            left: 0;
            z-index: 1000;
        }

        header h1 {
            margin: 0;
        }

        /* Sidebar Styling */
        #sidebar {
            width: 250px;
            position: fixed;
            top: 60px;
            left: 0;
            bottom: 0;
            background-color: #f4f4f4;
            padding: 20px;
            overflow-y: auto;
            border-right: 1px solid #ccc;
            background-color: #333;
            color: azure;

        }

        #sidebar button {
            width: 100%;
            padding: 10px;
            background-color: black;
            color: white;
            border: none;
            border-radius: 4px;
            margin-bottom: 15px;
            cursor: pointer;
        }

        #sidebar button:hover {
            background-color: darkgrey;
        }

        /* Chat Box Positioning */
        #chat-box {
            display: none;
            /* Hide the chat box initially */
            position: fixed;
            top: 60px;
            left: 292px;
            right: 0;
            bottom: 0;
            background-color: #333;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            flex-direction: column;
            z-index: 0;
            overflow-y: auto;
        }

        #messages {
            flex-grow: 1;
            overflow-y: auto;
            margin-bottom: 10px;
            padding-right: 10px;
        }

        #message-input,
        #chat-box button {
            display: none;
            /* Hide input field and send button initially */
        }

        #message-input {
            padding: 10px;
            margin-top: 10px;
            border: 1px solid #ccc;
            border-radius: 8px;
            width: 100%;
            box-sizing: border-box;
            position: sticky;
            bottom: 0;
            background-color: #333;
            color: whitesmoke;
        }

        #chat-box button {
            padding: 10px;
            background-color: black;
            color: whitesmoke;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 10px;
            position: sticky;
            bottom: 0;
        }

        #chat-box button:hover {
            background-color: darkgrey;
            color: azure;
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
            z-index: 2000;
        }

        .modal-content {
            background-color: #333;
            color: aliceblue;
            padding: 20px;
            border-radius: 8px;
            width: 250px;
            max-height: 80%;
            overflow-y: auto;
            z-index: 2001;
        }

        .modal-content span {
            cursor: pointer;
            color: aliceblue;
            font-size: 20px;
            border-radius: 1px;
        }

        .message {
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 8px;
            max-width: 70%;
            word-wrap: break-word;
        }

        .message.sent {
            background-color: darkgray;
            color: azure;
            align-self: flex-end;
        }

        .message.received {
            background-color: #2D5E97;
            color: azure;
            align-self: flex-start;
        }

        #messages {
            display: flex;
            flex-direction: column;
            gap: 10px;
            justify-content: flex-start;
            padding: 10px;
        }

        #userModal .modal-content img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 50%;
            margin-right: 10px;
            flex-shrink: 0;
        }

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

        #logoutButton {
            padding: 8px 16px;
            background-color: #333;
            color: azure;
            border: none;
            border-radius: 4px;
        }

        #logoutButton:hover {
            background-color: #660000;
            color: azure;
        }

        div::-webkit-scrollbar {
            background-color:black;
            width: 8px;
            /* Set the width of the scrollbar */
        }

        /* Track of the scrollbar */
        div::-webkit-scrollbar-track {
            background-color: #f1f1f1;
            /* Track color */
            border-radius: 10px;
            /* Rounded track corners */
        }

        /* Handle of the scrollbar */
        div::-webkit-scrollbar-thumb {
            background-color: #888;
            /* Scrollbar handle color */
            border-radius: 10px;
            /* Rounded thumb corners */
        }

        /* Handle on hover */
        div::-webkit-scrollbar-thumb:hover {
            background-color: #555;
            /* Darker color on hover */
        }
    </style>
</head>

<body>
    <header>
        <!--<h1>Welcome to Dashboard</h1>-->
        <img src="/storage/pictures/chatXlogo.png" alt="" width="7%">
        <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
            @csrf
            <button type="submit" id="logoutButton">Logout</button>
        </form>
    </header>

    <!-- Sidebar for Users and Chat List -->
    <div id="sidebar">
        <button id="new-chat-btn" onclick="openUserModal()">New Chat</button>
        <div id="user-list"></div>
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
        document.addEventListener("DOMContentLoaded", function() {
            fetchChats(); // Fetch chats on page load
        });

        Pusher.logToConsole = true;
        var pusher = new Pusher('24c0536b2bb45e29a90e', {
            cluster: 'ap2'
        });
        const userId = "{{ auth()->user()->id }}";
        const chatListChannel = pusher.subscribe(`chat-list.${userId}`);

        chatListChannel.bind('MessageSent', function(data) {
            console.log('New message sent:', data);
            fetchChats(); // Refresh the chat list
        });

        function openUserModal() {
            document.getElementById("userModal").style.display = "block";
            fetchUsers();
        }

        function closeUserModal() {
            document.getElementById("userModal").style.display = "none";
        }

        function fetchUsers() {
            fetch('{{ route('fetch.users') }}')
                .then(response => response.json())
                .then(data => {
                    const userListContainer = document.getElementById("user-list-modal");
                    userListContainer.innerHTML = "";

                    data.users.forEach(user => {
                        const userDiv = document.createElement("div");
                        userDiv.classList.add("user");
                        const imageUrl = user.picture ? `/storage/${user.picture}` :
                            '/storage/pictures/default.webp';
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
                    alert(data.message);
                    closeUserModal();
                    fetchChats();
                })
                .catch(error => console.log("Error starting chat:", error));
        }

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

        function displayChats(chats) {
            const chatListContainer = document.getElementById("chat-list");
            chatListContainer.innerHTML = "";
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
                const receiverId = chat.participants[0] == "{{ auth()->user()->id }}" ? chat.participants[1] : chat
                    .participants[0];
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

            // Show the chat box, input field, and send button
            document.getElementById("chat-box").style.display = "flex";
            document.getElementById("message-input").style.display = "block";
            document.querySelector("#chat-box button").style.display = "block";

            const userId = "{{ auth()->user()->id }}";

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
                    console.log(data.chats);
                    displayMessages(data.chats);
                    const messagesContainer = document.getElementById("messages");
                    messagesContainer.scrollTop = messagesContainer.scrollHeight;
                    subscribeToPusherChannel(receiverId);
                })
                .catch(error => console.log("Error fetching messages:", error));
        }

        function subscribeToPusherChannel(receiverId) {
            const senderId = {{ auth()->user()->id }};
            const channelName = 'chat.' + Math.min(senderId, receiverId) + '.' + Math.max(senderId, receiverId);
            console.log('Subscribing to channel:', channelName);

            var channel = pusher.subscribe(channelName);

            channel.bind('MessageSent', function(data) {
                console.log('Message received via Pusher:', data);
                const message = data.message;

                const messageElement = document.createElement('div');

                if (message.sender_id === "{{ auth()->user()->id }}") {
                    messageElement.classList.add('message', 'sent');
                } else {
                    messageElement.classList.add('message', 'received');
                }

                messageElement.innerHTML = `
            <p><strong>${message.sender_id === "{{ auth()->user()->id }}" ? "You" : 'User'}:</strong> ${message.content}</p>
        `;

                document.getElementById('messages').appendChild(messageElement);
                fetchChats();
            });
        }

        function sendMessage() {
            const messageInput = document.getElementById('message-input');
            const message = messageInput.value;
            const senderId = "{{ auth()->user()->id }}";
            const receiverId = window.currentReceiverId;

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
                    document.querySelector('#messages p').innerHTML = '';
                    fetchChats();
                })
                .catch(error => console.error('Error sending message:', error));
        }

        function displayMessages(chats) {
            const messagesContainer = document.getElementById("messages");
            messagesContainer.innerHTML = "";

            const messages = chats.messages || {};

            if (Object.keys(messages).length === 0) {
                messagesContainer.innerHTML = "<p>No messages yet.</p>";
            } else {
                Object.entries(messages).forEach(([key, message]) => {
                    const messageDiv = document.createElement("div");
                    messageDiv.classList.add("message");

                    if (message.sender_id === "{{ auth()->user()->id }}") {
                        messageDiv.classList.add("sent");
                    } else {
                        messageDiv.classList.add("received");
                    }

                    messageDiv.innerHTML = `
                <p><strong>${message.sender_id === "{{ auth()->user()->id }}" ? "You" : "User"}:</strong> ${message.content}</p>
            `;
                    messagesContainer.appendChild(messageDiv);
                });
            }
        }
    </script>
</body>

</html>

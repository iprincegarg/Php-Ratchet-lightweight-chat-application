<?php
session_start();

if (isset($_POST['username'])) {
    $_SESSION['username'] = htmlspecialchars($_POST['username']);
    header("Location: index.php");
    exit;
}

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}

$isLoggedIn = isset($_SESSION['username']);
$username = $isLoggedIn ? $_SESSION['username'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tabbed PHP Chat</title>
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background: #e9ecef; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            height: 100vh; 
            margin: 0; 
        }
        .app-container {
            display: flex;
            width: 100%;
            height: 100vh;
            background: #fff;
            overflow: hidden;
        }
        
        /* Sidebar Styles */
        .sidebar {
            width: 300px;
            background: #2c3e50;
            color: white;
            display: flex;
            flex-direction: column;
            border-right: 1px solid #1a252f;
        }
        .sidebar-header {
            padding: 20px;
            background: #1a252f;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .sidebar-header a {
            color: #e74c3c;
            text-decoration: none;
            font-size: 14px;
            font-weight: bold;
        }
        .add-connection {
            padding: 15px;
            background: #34495e;
            display: flex;
            gap: 10px;
        }
        .add-connection input {
            flex: 1;
            padding: 8px;
            border: none;
            border-radius: 4px;
            outline: none;
        }
        .add-connection button {
            padding: 8px 12px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .add-connection button:hover { background: #2980b9; }
        
        .list-section {
            padding: 15px;
            flex: 1;
            overflow-y: auto;
        }
        .list-section h3 {
            margin-top: 0;
            font-size: 14px;
            color: #95a5a6;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
        }
        
        .list-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            background: rgba(255,255,255,0.05);
            margin-bottom: 5px;
            border-radius: 6px;
            cursor: pointer;
            transition: background 0.2s;
        }
        .list-item:hover {
            background: rgba(255,255,255,0.1);
        }
        .list-item.active {
            background: #3498db;
        }
        .btn-accept { background: #2ecc71; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; font-size: 12px; }
        .btn-reject { background: #e74c3c; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; font-size: 12px; }
        
        /* Chat Area Styles */
        .chat-area {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: #f8f9fa;
        }
        .chat-header {
            padding: 20px;
            background: #fff;
            border-bottom: 1px solid #eee;
            font-size: 1.2em;
            font-weight: bold;
            color: #333;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .delete-btn {
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
            transition: transform 0.2s, color 0.2s;
            color: #95a5a6;
        }
        .delete-btn:hover {
            transform: scale(1.1);
            color: #e74c3c;
        }
        .messages {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        .message {
            padding: 12px 16px;
            border-radius: 12px;
            max-width: 75%;
            word-wrap: break-word;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        .message .user {
            font-weight: 600;
            font-size: 0.8em;
            margin-bottom: 4px;
            opacity: 0.8;
        }
        .message.self {
            align-self: flex-end;
            background: #3498db;
            color: white;
            border-bottom-right-radius: 2px;
        }
        .message.other {
            align-self: flex-start;
            background: #fff;
            color: #333;
            border: 1px solid #eee;
            border-bottom-left-radius: 2px;
        }
        .message img {
            max-width: 100%;
            border-radius: 8px;
            margin-top: 5px;
            display: block;
            max-height: 300px;
            object-fit: cover;
        }
        .input-area {
            padding: 20px;
            background: #fff;
            border-top: 1px solid #eee;
            display: flex;
            gap: 10px;
        }
        .input-area input {
            flex: 1;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 24px;
            outline: none;
            font-size: 15px;
        }
        .input-area input:focus { border-color: #3498db; }
        .input-area button {
            padding: 10px 20px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 24px;
            cursor: pointer;
            font-weight: bold;
        }
        .input-area button:hover { background: #2980b9; }
        
        .attach-btn {
            background: none !important;
            color: #95a5a6 !important;
            padding: 10px !important;
            font-size: 20px !important;
            border-radius: 50% !important;
            transition: color 0.2s;
        }
        .attach-btn:hover {
            color: #3498db !important;
            background: #f1f1f1 !important;
        }
        .placeholder-message {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100%;
            color: #999;
            font-size: 1.2em;
        }
        /* Image Modal Styles */
        .image-modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.85);
            justify-content: center;
            align-items: center;
        }
        .image-modal img {
            max-width: 90%;
            max-height: 90%;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.5);
            object-fit: contain;
        }
        .image-modal .close {
            position: absolute;
            top: 20px;
            right: 30px;
            color: #fff;
            font-size: 40px;
            font-weight: bold;
            cursor: pointer;
            user-select: none;
        }
        .image-modal .close:hover {
            color: #ccc;
        }
        /* Login styles */
        .login-container { 
            background: #fff; padding: 40px; border-radius: 12px; 
            box-shadow: 0 8px 24px rgba(0,0,0,0.1); text-align: center; width: 300px;
            max-width: 90%;
        }
        .login-container h2 { margin-top: 0; color: #333; margin-bottom: 25px; }
        .login-container input { 
            padding: 12px 15px; border: 1px solid #ddd; border-radius: 8px; 
            width: 100%; box-sizing: border-box; margin-bottom: 20px; outline: none; 
            font-size: 15px;
        }
        .login-container input:focus { border-color: #3498db; }
        .login-container button { 
            padding: 12px 20px; background: #3498db; color: white; border: none; 
            border-radius: 8px; cursor: pointer; font-weight: bold; width: 100%; 
            font-size: 16px; transition: background 0.2s;
        }
        .login-container button:hover { background: #2980b9; }
    </style>
</head>
<body>

<?php if (!$isLoggedIn): ?>
    <div class="login-container">
        <h2>Welcome to Chat</h2>
        <form method="POST" action="index.php">
            <input type="text" name="username" placeholder="Choose a username" required autocomplete="off">
            <button type="submit">Start Chat</button>
        </form>
    </div>
<?php else: ?>
    <div class="app-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <span><?php echo htmlspecialchars($username); ?></span>
                <a href="?logout=1">Logout</a>
            </div>
            
            <form class="add-connection" id="add-form">
                <input type="text" id="target-user" placeholder="Add user by name..." required autocomplete="off">
                <button type="submit">+</button>
            </form>

            <div class="list-section">
                <h3>Requests</h3>
                <div id="requests-list"></div>
                
                <h3 style="margin-top: 20px;">Chats</h3>
                <div id="chats-list"></div>
            </div>
        </div>

        <!-- Chat Area -->
        <div class="chat-area" id="chat-area">
            <div class="placeholder-message">Select a chat to start messaging</div>
        </div>
    </div>

    <!-- Image Modal -->
    <div id="image-modal" class="image-modal" onclick="this.style.display='none'">
        <span class="close">&times;</span>
        <img id="modal-img" src="" alt="Zoomed Image">
    </div>

    <script>
        const currentUser = "<?php echo addslashes($username); ?>";
        let activeChat = null;
        let isUserScrolling = false;
        let lastMessageCount = 0;
        let ws;

        function initWebSocket() {
            const protocol = window.location.protocol === 'https:' ? 'wss:' : 'ws:';
            let wsUrl;
            
            // If running locally, connect directly to port 8080
            if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
                wsUrl = 'ws://localhost:8080';
            } else {
                // If on production, use the reverse proxy endpoint
                wsUrl = protocol + '//' + window.location.host + '/ws';
            }

            ws = new WebSocket(wsUrl);
            ws.onopen = () => {
                ws.send(JSON.stringify({action: 'authenticate', user: currentUser}));
                loadRequests(); // Load initial state
            };
            ws.onmessage = (e) => {
                const data = JSON.parse(e.data);
                if (data.type === 'new_message') {
                    if (activeChat === data.chat || activeChat === data.from) {
                        appendMessage(data.message);
                    }
                    loadRequests(); // update ordering or anything else if needed
                } else if (data.type === 'reload_requests') {
                    loadRequests();
                    if (activeChat) loadMessages();
                }
            };
            ws.onclose = () => {
                console.log('WebSocket disconnected. Reconnecting in 2s...');
                setTimeout(initWebSocket, 2000); // Reconnect
            };
        }

        function appendMessage(msg) {
            const chatBox = document.getElementById('chat-box');
            if (!chatBox) return;
            const div = document.createElement('div');
            const isSelf = msg.user === currentUser;
            div.className = 'message ' + (isSelf ? 'self' : 'other');
            
            let contentHtml = `<div class="user">${msg.user}</div>`;
            if (msg.text) contentHtml += `<div class="text">${msg.text}</div>`;
            if (msg.image) contentHtml += `<img src="${msg.image}" alt="Attached Image" style="cursor: zoom-in;" onclick="zoomImage(this.src)">`;
            
            div.innerHTML = contentHtml;
            chatBox.appendChild(div);
            
            if (!isUserScrolling) {
                chatBox.scrollTop = chatBox.scrollHeight;
            }
        }

        function apiCall(data) {
            const formData = new FormData();
            for (const key in data) formData.append(key, data[key]);
            return fetch('api.php', { method: 'POST', body: formData }).then(r => r.json());
        }

        // Add Connection
        document.getElementById('add-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const target = document.getElementById('target-user').value.trim();
            if (!target) return;
            apiCall({action: 'send_request', target: target}).then(res => {
                if (res.status === 'error') alert(res.message);
                document.getElementById('target-user').value = '';
                ws.send(JSON.stringify({action: 'reload_requests', target: target}));
                loadRequests();
            });
        });

        // Load Requests and Chats
        function loadRequests() {
            fetch('api.php?action=get_requests')
                .then(r => r.json())
                .then(data => {
                    const requests = data.requests || [];
                    const requestsList = document.getElementById('requests-list');
                    const chatsList = document.getElementById('chats-list');
                    
                    requestsList.innerHTML = '';
                    chatsList.innerHTML = '';
                    
                    let hasChats = false;
                    let activeChatStillExists = false;

                    requests.forEach(req => {
                        const isSender = req.from === currentUser;
                        const otherUser = isSender ? req.to : req.from;

                        if (req.status === 'pending') {
                            const div = document.createElement('div');
                            div.className = 'list-item';
                            if (isSender) {
                                div.innerHTML = `<span>To: ${otherUser} (Pending)</span>`;
                            } else {
                                div.innerHTML = `
                                    <span>From: ${otherUser}</span>
                                    <div>
                                        <button class="btn-accept" onclick="respondRequest('${req.id}', '${otherUser}', 'accept')">✓</button>
                                        <button class="btn-reject" onclick="respondRequest('${req.id}', '${otherUser}', 'reject')">✗</button>
                                    </div>
                                `;
                            }
                            requestsList.appendChild(div);
                        } else if (req.status === 'accepted') {
                            hasChats = true;
                            if (activeChat === otherUser) activeChatStillExists = true;
                            const div = document.createElement('div');
                            div.className = 'list-item' + (activeChat === otherUser ? ' active' : '');
                            div.textContent = otherUser;
                            div.onclick = () => openChat(otherUser);
                            chatsList.appendChild(div);
                        }
                    });

                    if (activeChat && !activeChatStillExists) {
                        activeChat = null;
                        const chatArea = document.getElementById('chat-area');
                        if (chatArea) {
                            chatArea.innerHTML = '<div class="placeholder-message">Chat was deleted by the other user.</div>';
                        }
                    }

                    if (requestsList.innerHTML === '') {
                        requestsList.innerHTML = '<div style="font-size:12px;color:#aaa">No requests</div>';
                    }
                    if (chatsList.innerHTML === '') {
                        chatsList.innerHTML = '<div style="font-size:12px;color:#aaa">No active chats</div>';
                    }
                });
        }

        window.respondRequest = function(id, user, response) {
            apiCall({action: 'respond_request', id: id, response: response}).then(() => {
                ws.send(JSON.stringify({action: 'reload_requests', target: user}));
                loadRequests();
            });
        };

        window.deleteChat = function(user) {
            if (confirm(`Are you sure you want to permanently delete the chat with ${user} for both of you?`)) {
                apiCall({action: 'delete_chat', target: user}).then(res => {
                    if (res.status === 'ok') {
                        activeChat = null;
                        document.getElementById('chat-area').innerHTML = '<div class="placeholder-message">Select a chat to start messaging</div>';
                        ws.send(JSON.stringify({action: 'reload_requests', target: user}));
                        loadRequests();
                    } else {
                        alert("Error deleting chat.");
                    }
                });
            }
        };

        function openChat(user) {
            activeChat = user;
            loadRequests(); // Update active class
            
            const chatArea = document.getElementById('chat-area');
            chatArea.innerHTML = `
                <div class="chat-header">
                    <span>Chat with ${user}</span>
                    <button class="delete-btn" onclick="deleteChat('${user}')" title="Delete Chat">🗑️</button>
                </div>
                <div class="messages" id="chat-box"></div>
                <form class="input-area" id="chat-form">
                    <input type="file" id="image-input" accept="image/*" style="display:none">
                    <button type="button" class="attach-btn" onclick="document.getElementById('image-input').click()" title="Attach Image">📎</button>
                    <input type="text" id="message-input" placeholder="Type a message..." autocomplete="off">
                    <button type="submit">Send</button>
                </form>
            `;

            const chatBox = document.getElementById('chat-box');
            const attachBtn = document.querySelector('.attach-btn');
            const imageInput = document.getElementById('image-input');
            const chatForm = document.getElementById('chat-form');
            const messageInput = document.getElementById('message-input');

            imageInput.addEventListener('change', function() {
                if (this.files && this.files.length > 0) {
                    const file = this.files[0];
                    const formData = new FormData();
                    formData.append('action', 'upload_image');
                    formData.append('image', file);
                    
                    attachBtn.style.opacity = '0.5';

                    fetch('api.php', { method: 'POST', body: formData })
                        .then(r => r.json())
                        .then(res => {
                            attachBtn.style.opacity = '1';
                            if (res.status === 'error') {
                                alert(res.message);
                            } else {
                                ws.send(JSON.stringify({
                                    action: 'send_message',
                                    target: activeChat,
                                    image: res.image
                                }));
                                imageInput.value = '';
                            }
                        })
                        .catch(err => {
                            attachBtn.style.opacity = '1';
                            alert("An error occurred while uploading. Please check the file size.");
                        });
                }
            });

            chatBox.addEventListener('scroll', () => {
                const isAtBottom = chatBox.scrollHeight - chatBox.scrollTop - chatBox.clientHeight < 10;
                isUserScrolling = !isAtBottom;
            });

            document.getElementById('chat-form').addEventListener('submit', function(e) {
                e.preventDefault();
                const textInput = document.getElementById('message-input');
                const text = textInput.value.trim();
                
                if (!text) return;

                ws.send(JSON.stringify({
                    action: 'send_message',
                    target: activeChat,
                    text: text
                }));
                textInput.value = '';
            });

            isUserScrolling = false;
            lastMessageCount = 0;
            loadMessages();
        }

        function loadMessages() {
            if (!activeChat) return;
            fetch('api.php?action=get_messages&target=' + encodeURIComponent(activeChat))
                .then(r => r.json())
                .then(data => {
                    const messages = data.messages || [];
                    const chatBox = document.getElementById('chat-box');
                    if (!chatBox) return;

                    chatBox.innerHTML = '';
                    messages.forEach(msg => {
                        appendMessage(msg);
                    });
                    
                    if (!isUserScrolling) {
                        chatBox.scrollTop = chatBox.scrollHeight;
                        lastMessageCount = messages.length;
                    }
                });
        }
        
        function zoomImage(src) {
            const modal = document.getElementById('image-modal');
            const modalImg = document.getElementById('modal-img');
            modal.style.display = 'flex';
            modalImg.src = src;
        }

        initWebSocket();
    </script>
<?php endif; ?>

</body>
</html>

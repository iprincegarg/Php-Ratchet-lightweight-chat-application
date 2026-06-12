<?php
session_start();

if (isset($_POST['username'])) {
    $uname = strtolower(htmlspecialchars($_POST['username']));
    $uname = substr($uname, 0, 8);
    $_SESSION['username'] = $uname;
    
    $dataDir = __DIR__ . '/data';
    if (!file_exists($dataDir)) {
        mkdir($dataDir, 0777, true);
    }
    $usersFile = $dataDir . '/users.json';
    $users = [];
    if (file_exists($usersFile)) {
        $users = json_decode(file_get_contents($usersFile), true) ?: [];
    }
    if (!isset($users[$uname])) {
        $users[$uname] = ['joined' => time()];
        file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT), LOCK_EX);
    }

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
    <title>Secret & Secure Chat</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #e9ecef;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100dvh;
            margin: 0;
        }

        .app-container {
            display: flex;
            width: 100%;
            height: 100dvh;
            background: #fff;
            overflow: hidden;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 300px;
            min-width: 300px;
            background: #2c3e50;
            color: white;
            display: flex;
            flex-direction: column;
            border-right: 1px solid #1a252f;
            transition: margin-left 0.3s ease;
        }
        .sidebar.collapsed {
            margin-left: -300px;
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

        .add-connection button:hover {
            background: #2980b9;
        }

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
            background: rgba(255, 255, 255, 0.05);
            margin-bottom: 5px;
            border-radius: 6px;
            cursor: pointer;
            transition: background 0.2s;
        }

        .list-item:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .list-item.active {
            background: #3498db;
        }

        .btn-accept {
            background: #2ecc71;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
        }

        .btn-reject {
            background: #e74c3c;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
        }

        /* Chat Area Styles */
        .chat-area {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: #f8f9fa;
            position: relative;
        }
        .chat-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            height: 100%;
        }
        .toggle-sidebar-btn {
            position: absolute;
            top: 15px;
            left: 15px;
            z-index: 999;
            background: #fff;
            color: #2c3e50;
            border: 1px solid #ddd;
            border-radius: 6px;
            width: 36px;
            height: 36px;
            font-size: 20px;
            cursor: pointer;
            display: flex;
            justify-content: center;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            transition: all 0.2s;
        }
        .toggle-sidebar-btn:hover {
            background: #f8f9fa;
            border-color: #ccc;
        }
        .toggle-sidebar-btn svg {
            transition: transform 0.3s ease;
        }
        .sidebar:not(.collapsed) + .chat-area .toggle-sidebar-btn svg {
            transform: scaleX(-1);
        }

        .chat-header {
            padding: 20px 20px 20px 65px;
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
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        .message .user {
            font-weight: 600;
            font-size: 0.8em;
            margin-bottom: 4px;
            opacity: 0.8;
        }

        .message .time {
            font-size: 0.7em;
            opacity: 0.7;
            text-align: right;
            margin-top: 4px;
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

        .input-area input:focus {
            border-color: #3498db;
        }

        .input-area button {
            padding: 10px 20px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 24px;
            cursor: pointer;
            font-weight: bold;
        }

        .input-area button:hover {
            background: #2980b9;
        }

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
            background-color: rgba(0, 0, 0, 0.85);
            justify-content: center;
            align-items: center;
        }

        .image-modal img {
            max-width: 90%;
            max-height: 90%;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.5);
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
            background: #fff;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
            text-align: center;
            width: 300px;
            max-width: 90%;
        }

        .login-container h2 {
            margin-top: 0;
            color: #333;
            margin-bottom: 25px;
        }

        .login-container input {
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            width: 100%;
            box-sizing: border-box;
            margin-bottom: 20px;
            outline: none;
            font-size: 15px;
        }

        .login-container input:focus {
            border-color: #3498db;
        }

        .login-container button {
            padding: 12px 20px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            width: 100%;
            font-size: 16px;
            transition: background 0.2s;
        }

        .login-container button:hover {
            background: #2980b9;
        }

        /* Responsive Enterprise Design */
        @media (max-width: 768px) {
            .sidebar {
                position: absolute;
                z-index: 100;
                height: 100dvh;
                box-shadow: 2px 0 15px rgba(0,0,0,0.2);
            }
            .sidebar.collapsed {
                margin-left: -300px;
                box-shadow: none;
            }
            .sidebar:not(.collapsed) + .chat-area .toggle-sidebar-btn {
                display: none !important;
            }
            .mobile-close-btn {
                display: flex !important;
            }
            .chat-header {
                padding-left: 65px; /* Leave space for hamburger menu */
            }
            .input-area {
                padding: 15px;
            }
            .input-area input {
                padding: 10px 15px;
            }
            .input-area button {
                padding: 8px 15px;
            }
            .message {
                max-width: 85%;
            }
        }

    </style>
    <script type="module" src="https://cdn.jsdelivr.net/npm/emoji-picker-element@1.21.3/index.js"></script>
</head>

<body>

    <?php if (!$isLoggedIn): ?>
        <div class="login-container">
            <h2>Welcome to Chat</h2>
            <form method="POST" action="index.php">
                <input type="text" id="login-username" name="username" placeholder="Choose a username" maxlength="8" required autocomplete="off" style="margin-bottom: 5px;">
                <div id="char-count" style="text-align: right; font-size: 12px; color: #95a5a6; margin-bottom: 15px;">0 / 8 chars</div>
                <button type="submit">Start Chat</button>
            </form>
            <script>
                const loginInput = document.getElementById('login-username');
                const charCount = document.getElementById('char-count');
                loginInput.addEventListener('input', function() {
                    charCount.textContent = this.value.length + ' / 8 chars';
                    if (this.value.length === 8) {
                        charCount.style.color = '#e74c3c'; // Turns red when limit is reached
                    } else {
                        charCount.style.color = '#95a5a6';
                    }
                });
            </script>
        </div>
    <?php else: ?>
        <div class="app-container">
            <!-- Sidebar -->
            <div class="sidebar">
                <div class="sidebar-header">
                    <span style="display: flex; align-items: center; gap: 8px;">
                        <?php echo htmlspecialchars($username); ?>
                        <a href="#" onclick="renameUser()" title="Edit Username" style="color: #bdc3c7; text-decoration: none;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
                        </a>
                    </span>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <a href="#" class="mobile-close-btn" onclick="toggleSidebar()" title="Close Menu" style="color: #ecf0f1; text-decoration: none; display: none;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m11 17-5-5 5-5"/><path d="m18 17-5-5 5-5"/></svg>
                        </a>
                        <a href="#" onclick="deleteAccount()" title="Delete Account" style="color: #e74c3c; text-decoration: none; display: flex;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                        </a>
                        <a href="?logout=1" title="Logout" style="color: #ecf0f1; text-decoration: none; display: flex;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                        </a>
                    </div>
                </div>

                <form class="add-connection" id="add-form">
                    <input type="text" id="target-user" placeholder="Add user by name..." maxlength="8" required autocomplete="off">
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
        <div class="chat-area">
            <button class="toggle-sidebar-btn" onclick="toggleSidebar()" title="Toggle Sidebar">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m13 17 5-5-5-5"/><path d="M6 17l5-5-5-5"/></svg>
            </button>
            <div class="chat-content" id="chat-area">
                <div class="placeholder-message">Select a chat to start messaging</div>
            </div>
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
            let messageOffset = 0;
            let hasMoreMessages = false;
            let isLoadingMessages = false;
            let onlineUsers = [];

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
                    ws.send(JSON.stringify({ action: 'authenticate', user: currentUser }));
                    loadRequests(); // Load initial state
                };
                ws.onmessage = (e) => {
                    const data = JSON.parse(e.data);
                    if (data.type === 'new_message') {
                        if (activeChat === data.chat || activeChat === data.from) {
                            appendMessage(data.message);
                            messageOffset++;
                            if (activeChat === data.from) {
                                if (document.hasFocus()) {
                                    ws.send(JSON.stringify({ action: 'mark_read', target: activeChat }));
                                }
                            }
                        }
                        loadRequests(); // update ordering or anything else if needed
                    } else if (data.type === 'reload_all') {
                        window.location.reload();
                    } else if (data.type === 'online_list') {
                        onlineUsers = data.users;
                        loadRequests();
                        if (activeChat) {
                            const hdr = document.getElementById('chat-header-status');
                            if (hdr) {
                                hdr.textContent = onlineUsers.includes(activeChat) ? 'Online' : 'Offline';
                                hdr.style.color = onlineUsers.includes(activeChat) ? '#2ecc71' : '#bdc3c7';
                            }
                        }
                    } else if (data.type === 'user_status') {
                        if (data.status === 'online') {
                            if (!onlineUsers.includes(data.user)) onlineUsers.push(data.user);
                        } else {
                            onlineUsers = onlineUsers.filter(u => u !== data.user);
                        }
                        loadRequests();
                        if (activeChat === data.user) {
                            const hdr = document.getElementById('chat-header-status');
                            if (hdr) {
                                hdr.textContent = data.status === 'online' ? 'Online' : 'Offline';
                                hdr.style.color = data.status === 'online' ? '#2ecc71' : '#bdc3c7';
                            }
                        }
                    } else if (data.type === 'read_receipt') {
                        if (activeChat === data.from) {
                            const sentTicks = document.querySelectorAll('.message.self .status-tick.sent');
                            sentTicks.forEach(tick => {
                                tick.outerHTML = `<svg class="status-tick read" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 4 12 14.01 9 11.01"></polyline><polyline points="16 4 6 14.01 3 11.01"></polyline></svg>`;
                            });
                        }
                    } else if (data.type === 'typing') {
                        if (activeChat === data.from) {
                            const ti = document.getElementById('typing-indicator');
                            if (ti) {
                                ti.textContent = data.from + ' is typing...';
                                ti.style.display = 'block';
                            }
                        }
                    } else if (data.type === 'stop_typing') {
                        if (activeChat === data.from) {
                            const ti = document.getElementById('typing-indicator');
                            if (ti) ti.style.display = 'none';
                        }
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

            function createMessageElement(msg) {
                const div = document.createElement('div');
                const isSelf = msg.user === currentUser;
                div.className = 'message ' + (isSelf ? 'self' : 'other');

                let contentHtml = `<div class="user">${msg.user}</div>`;
                if (msg.text) {
                    const isImgUrl = msg.text.match(/^https?:\/\/.+\.(gif|png|jpe?g|webp)(\?.*)?$/i) || msg.text.includes('tenor.com') || msg.text.includes('giphy.com');
                    if (isImgUrl) {
                        contentHtml += `<img src="${msg.text}" alt="GIF/Image" style="cursor: zoom-in; max-width: 250px; border-radius: 8px;" onclick="zoomImage(this.src)">`;
                    } else {
                        contentHtml += `<div class="text">${msg.text}</div>`;
                    }
                }
                if (msg.image) contentHtml += `<img src="${msg.image}" alt="Attached Image" style="cursor: zoom-in;" onclick="zoomImage(this.src)">`;

                if (msg.time) {
                    const date = new Date(msg.time * 1000);
                    const day = String(date.getDate()).padStart(2, '0');
                    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                    const month = months[date.getMonth()];
                    const year = date.getFullYear();
                    const hours = String(date.getHours()).padStart(2, '0');
                    const minutes = String(date.getMinutes()).padStart(2, '0');
                    let timeHtml = `<div class="time" style="display: flex; align-items: center; justify-content: flex-end; gap: 4px;">${day} ${month} ${year} ${hours}:${minutes}`;
                    
                    if (isSelf) {
                        if (msg.status === 'read') {
                            timeHtml += `<svg class="status-tick read" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 4 12 14.01 9 11.01"></polyline><polyline points="16 4 6 14.01 3 11.01"></polyline></svg>`;
                        } else {
                            timeHtml += `<svg class="status-tick sent" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>`;
                        }
                    }
                    timeHtml += `</div>`;
                    contentHtml += timeHtml;
                }

                div.innerHTML = contentHtml;
                return div;
            }

            function appendMessage(msg) {
                const chatBox = document.getElementById('chat-box');
                if (!chatBox) return;
                const div = createMessageElement(msg);
                chatBox.appendChild(div);

                if (!isUserScrolling) {
                    chatBox.scrollTop = chatBox.scrollHeight;
                }
            }

            function prependMessage(msg) {
                const chatBox = document.getElementById('chat-box');
                if (!chatBox) return;
                const div = createMessageElement(msg);
                chatBox.insertBefore(div, chatBox.firstChild);
            }

            function apiCall(data) {
                const formData = new FormData();
                for (const key in data) formData.append(key, data[key]);
                return fetch('api.php', { method: 'POST', body: formData }).then(r => r.json());
            }

            // Add Connection
            document.getElementById('add-form').addEventListener('submit', function (e) {
                e.preventDefault();
                const target = document.getElementById('target-user').value.trim();
                if (!target) return;
                apiCall({ action: 'send_request', target: target }).then(res => {
                    if (res.status === 'error') alert(res.message);
                    document.getElementById('target-user').value = '';
                    ws.send(JSON.stringify({ action: 'reload_requests', target: target }));
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
                                
                                const dotColor = onlineUsers.includes(otherUser) ? '#2ecc71' : '#bdc3c7';
                                div.innerHTML = `
                                    <div style="display: flex; align-items: center; justify-content: space-between; width: 100%;">
                                        <span>${otherUser}</span>
                                        <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background-color: ${dotColor};"></span>
                                    </div>
                                `;
                                
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

            window.respondRequest = function (id, user, response) {
                apiCall({ action: 'respond_request', id: id, response: response }).then(() => {
                    ws.send(JSON.stringify({ action: 'reload_requests', target: user }));
                    loadRequests();
                });
            };

            window.deleteChat = function (user) {
                if (confirm(`Are you sure you want to permanently delete the chat history with ${user} for both of you?`)) {
                    apiCall({ action: 'delete_chat', target: user }).then(res => {
                        if (res.status === 'ok') {
                            ws.send(JSON.stringify({ action: 'reload_requests', target: user }));
                            loadMessages();
                        } else {
                            alert("Error deleting chat history.");
                        }
                    });
                }
            };

            window.deleteConnection = function (user) {
                if (confirm(`Are you sure you want to permanently remove the connection with ${user}? You will no longer be able to message each other.`)) {
                    apiCall({ action: 'delete_connection', target: user }).then(res => {
                        if (res.status === 'ok') {
                            activeChat = null;
                            document.getElementById('chat-area').innerHTML = '<div class="placeholder-message">Select a chat to start messaging</div>';
                            ws.send(JSON.stringify({ action: 'reload_requests', target: user }));
                            loadRequests();
                        } else {
                            alert("Error removing connection.");
                        }
                    });
                }
            };

            window.deleteAccount = function () {
                if (confirm("Are you sure you want to permanently delete your account and all associated data? This action cannot be undone.")) {
                    apiCall({ action: 'delete_account' }).then(res => {
                        if (res.status === 'ok') {
                            if (res.affected && res.affected.length > 0) {
                                res.affected.forEach(user => {
                                    ws.send(JSON.stringify({ action: 'reload_requests', target: user }));
                                });
                            }
                            // Redirect to logout to clear session
                            window.location.href = 'index.php?logout=1';
                        } else {
                            alert("Error deleting account.");
                        }
                    });
                }
            };

            window.renameUser = function() {
                const newName = prompt("Enter a new username (max 8 characters, alphanumeric and underscores only):");
                if (!newName) return;
                
                const formData = new FormData();
                formData.append('action', 'rename_user');
                formData.append('new_username', newName);
                
                fetch('api.php', { method: 'POST', body: formData })
                    .then(r => r.json())
                    .then(res => {
                        if (res.status === 'ok') {
                            if (ws && ws.readyState === WebSocket.OPEN) {
                                ws.send(JSON.stringify({ action: 'force_reload_all' }));
                            }
                            window.location.reload();
                        } else {
                            alert(res.message);
                        }
                    })
                    .catch(err => {
                        alert("An error occurred during renaming.");
                    });
            };

            function openChat(user) {
                activeChat = user;
                loadRequests(); // Update active class

                const chatArea = document.getElementById('chat-area');
                const statusStr = onlineUsers.includes(user) ? 'Online' : 'Offline';
                const statusColor = onlineUsers.includes(user) ? '#2ecc71' : '#bdc3c7';
                
                chatArea.innerHTML = `
                <div class="chat-header">
                    <div style="display: flex; flex-direction: column;">
                        <span style="font-weight: bold;">Chat with ${user}</span>
                        <span id="chat-header-status" style="font-size: 12px; color: ${statusColor}; margin-top: 2px;">${statusStr}</span>
                    </div>
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <button class="delete-btn" onclick="deleteChat('${user}')" title="Delete Chat History" style="display: flex; margin: 0;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                        </button>
                        <button class="delete-btn" onclick="deleteConnection('${user}')" title="Remove Connection" style="display: flex; margin: 0;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"></path><path d="m6 6 12 12"></path></svg>
                        </button>
                    </div>
                </div>
                <div class="messages" id="chat-box"></div>
                <div id="typing-indicator" style="display: none; padding: 0 20px 10px 20px; font-size: 13px; color: #95a5a6; font-style: italic;"></div>
                <form class="input-area" id="chat-form">
                    <input type="file" id="image-input" accept="image/*" style="display:none">
                    <button type="button" class="attach-btn" onclick="document.getElementById('image-input').click()" title="Attach Image">📎</button>
                    <button type="button" class="attach-btn" id="gif-btn" title="Add GIF" style="font-weight: bold; font-size: 14px;">GIF</button>
                    <button type="button" class="attach-btn" id="emoji-btn" title="Add Emoji">😀</button>
                    
                    <div id="emoji-picker-container" style="display: none; position: absolute; bottom: 80px; left: 20px; z-index: 1000; box-shadow: 0 4px 15px rgba(0,0,0,0.2); border-radius: 8px; overflow: hidden; background: white;">
                        <emoji-picker></emoji-picker>
                    </div>

                    <div id="gif-picker-container" style="display: none; position: absolute; bottom: 80px; left: 60px; z-index: 1000; background: white; width: 300px; height: 350px; box-shadow: 0 4px 15px rgba(0,0,0,0.2); border-radius: 8px; flex-direction: column;">
                        <div style="display: flex; border-bottom: 1px solid #ddd;">
                            <select id="gif-provider" style="border: none; outline: none; background: #f8f9fa; padding: 10px; border-radius: 8px 0 0 0; cursor: pointer; border-right: 1px solid #ddd;">
                                <option value="tenor">Tenor</option>
                                <option value="giphy">Giphy</option>
                            </select>
                            <input type="text" id="gif-search" placeholder="Search GIFs..." style="flex: 1; box-sizing: border-box; padding: 10px; border: none; outline: none; border-radius: 0 8px 0 0;">
                        </div>
                        <div id="gif-results" style="flex: 1; overflow-y: auto; display: flex; flex-wrap: wrap; gap: 5px; padding: 5px;"></div>
                    </div>

                    <input type="text" id="message-input" placeholder="Type a message..." autocomplete="off">
                    <button type="submit">Send</button>
                </form>
            `;

                const chatBox = document.getElementById('chat-box');
                const messageInput = document.getElementById('message-input');
                const chatForm = document.getElementById('chat-form');
                const imageInput = document.getElementById('image-input');
                
                // Emoji & GIF logic
                const emojiBtn = document.getElementById('emoji-btn');
                const emojiContainer = document.getElementById('emoji-picker-container');
                const picker = document.querySelector('emoji-picker');

                const gifBtn = document.getElementById('gif-btn');
                const gifContainer = document.getElementById('gif-picker-container');
                const gifSearch = document.getElementById('gif-search');
                const gifResults = document.getElementById('gif-results');

                emojiBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    emojiContainer.style.display = emojiContainer.style.display === 'none' ? 'block' : 'none';
                    gifContainer.style.display = 'none';
                });

                gifBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    if (gifContainer.style.display === 'none') {
                        gifContainer.style.display = 'flex';
                        emojiContainer.style.display = 'none';
                        if (gifResults.innerHTML === '') searchGifs('trending');
                    } else {
                        gifContainer.style.display = 'none';
                    }
                });

                let gifTimeout;
                gifSearch.addEventListener('input', () => {
                    clearTimeout(gifTimeout);
                    gifTimeout = setTimeout(() => {
                        const q = gifSearch.value.trim();
                        searchGifs(q ? q : 'trending');
                    }, 500);
                });

                const gifProvider = document.getElementById('gif-provider');

                gifProvider.addEventListener('change', () => {
                    const q = gifSearch.value.trim();
                    searchGifs(q ? q : 'trending');
                });

                function searchGifs(query) {
                    gifResults.innerHTML = '<div style="padding: 10px; color: #999;">Loading...</div>';
                    const provider = gifProvider.value;
                    let url = '';

                    if (provider === 'tenor') {
                        url = query === 'trending' 
                            ? `https://g.tenor.com/v1/trending?key=LIVDSRZULELA&limit=20`
                            : `https://g.tenor.com/v1/search?q=${encodeURIComponent(query)}&key=LIVDSRZULELA&limit=20`;
                    } else {
                        url = query === 'trending'
                            ? `https://api.giphy.com/v1/gifs/trending?api_key=Gc7131jiJuvI7IdN0HZ1D7nh0ow5BU6g&limit=20`
                            : `https://api.giphy.com/v1/gifs/search?api_key=Gc7131jiJuvI7IdN0HZ1D7nh0ow5BU6g&q=${encodeURIComponent(query)}&limit=20`;
                    }
                    
                    fetch(url).then(r => r.json()).then(data => {
                        gifResults.innerHTML = '';
                        const items = provider === 'tenor' ? data.results : data.data;
                        
                        if (items && items.length > 0) {
                            items.forEach(gif => {
                                const img = document.createElement('img');
                                img.src = provider === 'tenor' ? gif.media[0].tinygif.url : gif.images.fixed_height_small.url;
                                img.style.height = '80px';
                                img.style.cursor = 'pointer';
                                img.style.borderRadius = '4px';
                                img.onclick = () => {
                                    const gifUrl = provider === 'tenor' ? gif.media[0].mediumgif.url : gif.images.original.url;
                                    ws.send(JSON.stringify({
                                        action: 'send_message',
                                        target: activeChat,
                                        text: gifUrl
                                    }));
                                    gifContainer.style.display = 'none';
                                };
                                gifResults.appendChild(img);
                            });
                        } else {
                            gifResults.innerHTML = '<div style="padding: 10px; color: #999;">No GIFs found</div>';
                        }
                    }).catch(e => {
                        gifResults.innerHTML = '<div style="padding: 10px; color: red;">Error loading GIFs</div>';
                    });
                }

                picker.addEventListener('emoji-click', event => {
                    messageInput.value += event.detail.unicode;
                    messageInput.focus();
                });

                document.addEventListener('click', (e) => {
                    if (emojiBtn && e.target !== emojiBtn && !emojiContainer.contains(e.target)) {
                        emojiContainer.style.display = 'none';
                    }
                    if (gifBtn && e.target !== gifBtn && !gifContainer.contains(e.target)) {
                        gifContainer.style.display = 'none';
                    }
                });

                const attachBtn = document.querySelector('.attach-btn');

                imageInput.addEventListener('change', function () {
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
                    if (chatBox.scrollTop === 0 && hasMoreMessages && !isLoadingMessages) {
                        loadMoreMessages();
                    }
                    const isAtBottom = chatBox.scrollHeight - chatBox.scrollTop - chatBox.clientHeight < 10;
                    isUserScrolling = !isAtBottom;
                });

                let typingTimer;
                let isTyping = false;
                
                messageInput.addEventListener('input', () => {
                    if (!isTyping) {
                        isTyping = true;
                        ws.send(JSON.stringify({ action: 'typing', target: activeChat }));
                    }
                    clearTimeout(typingTimer);
                    typingTimer = setTimeout(() => {
                        isTyping = false;
                        ws.send(JSON.stringify({ action: 'stop_typing', target: activeChat }));
                    }, 1500);
                });

                document.getElementById('chat-form').addEventListener('submit', function (e) {
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
                    
                    isTyping = false;
                    clearTimeout(typingTimer);
                    ws.send(JSON.stringify({ action: 'stop_typing', target: activeChat }));
                });

                isUserScrolling = false;
                lastMessageCount = 0;
                loadMessages();
                if (document.hasFocus()) {
                    ws.send(JSON.stringify({ action: 'mark_read', target: activeChat }));
                }
            }

            function loadMessages() {
                if (!activeChat) return;
                messageOffset = 0;
                isLoadingMessages = true;
                fetch('api.php?action=get_messages&target=' + encodeURIComponent(activeChat) + '&offset=' + messageOffset)
                    .then(r => r.json())
                    .then(data => {
                        const messages = data.messages || [];
                        hasMoreMessages = data.has_more || false;
                        const chatBox = document.getElementById('chat-box');
                        if (!chatBox) return;

                        chatBox.innerHTML = '';
                        messages.forEach(msg => {
                            appendMessage(msg);
                        });

                        messageOffset += messages.length;

                        if (!isUserScrolling) {
                            chatBox.scrollTop = chatBox.scrollHeight;
                            lastMessageCount = messages.length;
                        }
                        isLoadingMessages = false;
                    })
                    .catch(() => { isLoadingMessages = false; });
            }

            function loadMoreMessages() {
                if (!activeChat || !hasMoreMessages || isLoadingMessages) return;
                isLoadingMessages = true;
                
                // Keep the old scroll height to restore position after prepending
                const chatBox = document.getElementById('chat-box');
                const oldScrollHeight = chatBox ? chatBox.scrollHeight : 0;

                fetch('api.php?action=get_messages&target=' + encodeURIComponent(activeChat) + '&offset=' + messageOffset)
                    .then(r => r.json())
                    .then(data => {
                        const messages = data.messages || [];
                        hasMoreMessages = data.has_more || false;
                        if (!chatBox || messages.length === 0) {
                            isLoadingMessages = false;
                            return;
                        }

                        // Prepend messages in reverse order so they appear chronologically above
                        messages.reverse().forEach(msg => {
                            prependMessage(msg);
                        });

                        messageOffset += messages.length;

                        // Maintain scroll position so it doesn't jump to top
                        const newScrollHeight = chatBox.scrollHeight;
                        chatBox.scrollTop = newScrollHeight - oldScrollHeight;

                        isLoadingMessages = false;
                    })
                    .catch(() => { isLoadingMessages = false; });
            }

            function zoomImage(src) {
            const modal = document.getElementById('image-modal');
            const modalImg = document.getElementById('modal-img');
            modal.style.display = 'flex';
            modalImg.src = src;
        }

        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            sidebar.classList.toggle('collapsed');
        }

        initWebSocket();
        
        window.addEventListener('focus', () => {
            if (activeChat && ws && ws.readyState === WebSocket.OPEN) {
                ws.send(JSON.stringify({ action: 'mark_read', target: activeChat }));
            }
        });
        </script>
    <?php endif; ?>

</body>

</html>
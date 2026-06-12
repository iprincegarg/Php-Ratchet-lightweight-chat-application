# Real-Time PHP Chat Application

A lightweight, real-time 1-on-1 chat application built with PHP, Vanilla JavaScript, and WebSockets (Ratchet). It features a tabbed interface, image attachments with zoom, connection requests, and instant messaging without the need for a traditional database (uses flat JSON file storage).

## Features
- **Real-Time Messaging:** Powered by WebSockets (Ratchet PHP) for instant delivery without HTTP polling.
- **1-on-1 Chats & Connection Requests:** Users can add each other via username. Connections must be accepted before chatting.
- **Tabbed Interface:** Seamlessly switch between multiple active conversations.
- **Image Attachments:** Send images up to 2MB directly in the chat.
- **Lightbox Zoom:** Click on any image in the chat to view it in full screen.
- **Flat-File Storage:** No MySQL required! All messages, requests, and users are stored locally in the `data/` directory using JSON.

## Requirements
- PHP >= 8.1
- Composer
- Web Server (Nginx or Apache)
- Supervisor (for running the WebSocket process in production)

## Installation (Local Development)

1. **Clone the repository:**
   ```bash
   git clone <your-repo-url>
   cd chat
   ```

2. **Install Dependencies:**
   Install Ratchet WebSocket library via Composer.
   ```bash
   composer install
   ```

3. **Start the Web Server:**
   Start the built-in PHP server for the frontend HTTP requests.
   ```bash
   php -S localhost:8000
   ```

4. **Start the WebSocket Server:**
   In a separate terminal window, run the WebSocket process.
   ```bash
   php server.php
   ```

5. **Open in Browser:**
   Navigate to `http://localhost:8000`

## Production Deployment (Nginx & Supervisor)

To run this in production, you must use a reverse proxy to handle secure WebSocket connections (`wss://`). 

1. **Keep WebSocket Alive:**
   Configure Supervisor to keep `server.php` running in the background.
   ```ini
   [program:chat-websocket]
   command=/usr/bin/php /path/to/your/project/server.php
   autostart=true
   autorestart=true
   user=www-data
   ```

2. **Nginx Reverse Proxy:**
   Add a location block to your Nginx configuration to proxy `/ws` to port `8080`.
   ```nginx
   location /ws {
       proxy_pass http://127.0.0.1:8080;
       proxy_http_version 1.1;
       proxy_set_header Upgrade $http_upgrade;
       proxy_set_header Connection "upgrade";
       proxy_set_header Host $host;
       proxy_read_timeout 86400;
   }
   ```

## Architecture / Folder Structure
- `index.php`: The main frontend interface and login screen.
- `api.php`: The HTTP controller handling file uploads and initial data fetching.
- `server.php`: The bootstrapper script for the Ratchet WebSocket server.
- `src/Chat.php`: The core WebSocket routing and event handling logic.
- `data/`: Auto-generated folder containing `users.json`, `requests.json`, `chats/`, and `uploads/`. (This directory is ignored in Git).

<?php
namespace MyApp;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class Chat implements MessageComponentInterface {
    protected $clients;
    protected $userConnections; // map username to connection resource id
    protected $dataDir;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->userConnections = [];
        $this->dataDir = __DIR__ . '/../data';
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $data = json_decode($msg, true);
        if (!$data) return;

        $action = $data['action'] ?? '';
        $user = $data['user'] ?? '';

        if ($action === 'authenticate' && $user) {
            $from->username = $user;
            $this->userConnections[$user] = $from;
            echo "User authenticated: {$user} ({$from->resourceId})\n";
            return;
        }

        if (!isset($from->username)) return;

        $currentUser = $from->username;

        if ($action === 'send_message') {
            $targetUser = $data['target'] ?? '';
            $text = $data['text'] ?? '';
            $imagePath = $data['image'] ?? null;

            if (empty($targetUser) || (empty($text) && empty($imagePath))) return;

            // Verify connection
            $requestsFile = $this->dataDir . '/requests.json';
            $requests = file_exists($requestsFile) ? json_decode(file_get_contents($requestsFile), true) : [];
            $connected = false;
            foreach ($requests as $req) {
                if ($req['status'] === 'accepted' && 
                    (($req['from'] === $currentUser && $req['to'] === $targetUser) || 
                     ($req['from'] === $targetUser && $req['to'] === $currentUser))) {
                    $connected = true;
                    break;
                }
            }
            if (!$connected) return;

            // Save message
            $chatsDir = $this->dataDir . '/chats';
            if (!file_exists($chatsDir)) mkdir($chatsDir, 0777, true);
            
            $users = [$currentUser, $targetUser];
            sort($users);
            $chatFile = $chatsDir . '/' . md5($users[0] . '_' . $users[1]) . '.json';
            $messages = file_exists($chatFile) ? json_decode(file_get_contents($chatFile), true) : [];
            
            $messageData = [
                'user' => $currentUser,
                'text' => $text,
                'time' => time()
            ];
            if ($imagePath) {
                $messageData['image'] = $imagePath;
            }
            $messages[] = $messageData;
            file_put_contents($chatFile, json_encode($messages, JSON_PRETTY_PRINT));

            // Broadcast to target and sender
            $payload = json_encode([
                'type' => 'new_message',
                'chat' => $targetUser, // context for sender
                'from' => $currentUser, // context for receiver
                'message' => $messageData
            ]);

            $from->send($payload);
            if (isset($this->userConnections[$targetUser])) {
                $this->userConnections[$targetUser]->send($payload);
            }
        }
        else if ($action === 'reload_requests') {
            $targetUser = $data['target'] ?? '';
            if ($targetUser && isset($this->userConnections[$targetUser])) {
                $this->userConnections[$targetUser]->send(json_encode(['type' => 'reload_requests']));
            }
            $from->send(json_encode(['type' => 'reload_requests']));
        }
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        if (isset($conn->username)) {
            unset($this->userConnections[$conn->username]);
            echo "User disconnected: {$conn->username}\n";
        } else {
            echo "Connection {$conn->resourceId} has disconnected\n";
        }
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }
}

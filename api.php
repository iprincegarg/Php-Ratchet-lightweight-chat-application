<?php
session_start();

$dataDir = __DIR__ . '/data';
$chatsDir = $dataDir . '/chats';
$uploadsDir = $dataDir . '/uploads';

if (!file_exists($dataDir)) {
    mkdir($dataDir, 0777, true);
}
if (!file_exists($chatsDir)) {
    mkdir($chatsDir, 0777, true);
}
if (!file_exists($uploadsDir)) {
    mkdir($uploadsDir, 0777, true);
}

$usersFile = $dataDir . '/users.json';
$requestsFile = $dataDir . '/requests.json';

// Initialize files if they don't exist
if (!file_exists($usersFile)) {
    file_put_contents($usersFile, json_encode([]));
}
if (!file_exists($requestsFile)) {
    file_put_contents($requestsFile, json_encode([]));
}

// Helper to get helper functions
function readJson($file) {
    return json_decode(file_get_contents($file), true) ?: [];
}

function writeJson($file, $data) {
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT), LOCK_EX);
}

header('Content-Type: application/json');

if (!isset($_SESSION['username'])) {
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

$currentUser = $_SESSION['username'];
$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '');

// Actions that remain HTTP:
// - send_request
// - get_requests
// - respond_request
// - upload_image
// - get_messages
// - delete_chat

// Action: Send Request
if ($action === 'send_request') {
    $targetUser = isset($_POST['target']) ? strtolower(trim($_POST['target'])) : '';
    if (empty($targetUser) || $targetUser === $currentUser) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid target user.']);
        exit;
    }
    
    // Check if target user exists
    $users = readJson($usersFile);
    if (!isset($users[$targetUser])) {
        echo json_encode(['status' => 'error', 'message' => 'User not found.']);
        exit;
    }

    $requests = readJson($requestsFile);
    // Check if request already exists
    foreach ($requests as $req) {
        if (($req['from'] === $currentUser && $req['to'] === $targetUser) || 
            ($req['from'] === $targetUser && $req['to'] === $currentUser)) {
            echo json_encode(['status' => 'error', 'message' => 'Request or connection already exists.']);
            exit;
        }
    }

    $requests[] = [
        'id' => uniqid(),
        'from' => $currentUser,
        'to' => $targetUser,
        'status' => 'pending',
        'time' => time()
    ];
    writeJson($requestsFile, $requests);
    echo json_encode(['status' => 'ok']);
    exit;
}

// Action: Get Requests (Pending and Accepted)
if ($action === 'get_requests') {
    $requests = readJson($requestsFile);
    $myRequests = [];
    foreach ($requests as $req) {
        if ($req['from'] === $currentUser || $req['to'] === $currentUser) {
            $myRequests[] = $req;
        }
    }
    echo json_encode(['requests' => $myRequests]);
    exit;
}

// Action: Respond to Request
if ($action === 'respond_request') {
    $reqId = isset($_POST['id']) ? $_POST['id'] : '';
    $response = isset($_POST['response']) ? $_POST['response'] : ''; // 'accept' or 'reject'
    
    $requests = readJson($requestsFile);
    $found = false;
    foreach ($requests as &$req) {
        if ($req['id'] === $reqId && $req['to'] === $currentUser && $req['status'] === 'pending') {
            if ($response === 'accept') {
                $req['status'] = 'accepted';
            } else {
                $req['status'] = 'rejected';
            }
            $found = true;
            break;
        }
    }
    
    // Clean up rejected requests
    $requests = array_filter($requests, function($r) {
        return $r['status'] !== 'rejected';
    });
    
    writeJson($requestsFile, array_values($requests));
    echo json_encode(['status' => $found ? 'ok' : 'error']);
    exit;
}

// Helper for chat file name
function getChatFile($user1, $user2, $chatsDir) {
    $users = [$user1, $user2];
    sort($users);
    return $chatsDir . '/' . md5($users[0] . '_' . $users[1]) . '.json';
}

// Action: Upload Image
if ($action === 'upload_image') {
    if (isset($_FILES['image'])) {
        if ($_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $tmpName = $_FILES['image']['tmp_name'];
            $size = $_FILES['image']['size'];
            $type = $_FILES['image']['type'];
            
            if ($size > 2 * 1024 * 1024) {
                echo json_encode(['status' => 'error', 'message' => 'Image size exceeds 2MB limit.']);
                exit;
            }
            
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!in_array($type, $allowedTypes)) {
                echo json_encode(['status' => 'error', 'message' => 'Invalid image format.']);
                exit;
            }
            
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            if (!$ext) $ext = explode('/', $type)[1];
            
            $fileName = uniqid() . '.' . $ext;
            $destPath = $uploadsDir . '/' . $fileName;
            
            if (move_uploaded_file($tmpName, $destPath)) {
                echo json_encode(['status' => 'ok', 'image' => 'data/uploads/' . $fileName]);
                exit;
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to move uploaded file.']);
                exit;
            }
        } else if ($_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
            echo json_encode(['status' => 'error', 'message' => 'Upload failed: ' . $_FILES['image']['error']]);
            exit;
        }
    }
    echo json_encode(['status' => 'error', 'message' => 'No valid image provided.']);
    exit;
}

// Action: Get Messages
if ($action === 'get_messages') {
    $targetUser = isset($_GET['target']) ? strtolower(trim($_GET['target'])) : '';
    if (empty($targetUser)) {
        echo json_encode(['messages' => [], 'has_more' => false]);
        exit;
    }
    
    $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
    $limit = 20;

    $chatFile = getChatFile($currentUser, $targetUser, $chatsDir);
    $messages = file_exists($chatFile) ? readJson($chatFile) : [];
    
    $total = count($messages);
    $start = max(0, $total - $offset - $limit);
    $length = min($limit, $total - $offset);
    
    $pageMessages = $length > 0 ? array_slice($messages, $start, $length) : [];

    echo json_encode(['messages' => $pageMessages, 'has_more' => $start > 0]);
    exit;
}

// Action: Delete Chat
if ($action === 'delete_chat') {
    $targetUser = isset($_POST['target']) ? strtolower(trim($_POST['target'])) : '';
    if (empty($targetUser)) {
        echo json_encode(['status' => 'error']);
        exit;
    }
    
    // Do not remove the connection request, only delete the chat file and associated media

    // Delete chat file and associated media
    $chatFile = getChatFile($currentUser, $targetUser, $chatsDir);
    if (file_exists($chatFile)) {
        $messages = readJson($chatFile);
        foreach ($messages as $msg) {
            if (!empty($msg['image'])) {
                $imgPath = __DIR__ . '/' . $msg['image'];
                if (file_exists($imgPath)) {
                    unlink($imgPath);
                }
            }
        }
        unlink($chatFile);
    }
    
    echo json_encode(['status' => 'ok']);
    exit;
}

// Action: Delete Connection
if ($action === 'delete_connection') {
    $targetUser = isset($_POST['target']) ? strtolower(trim($_POST['target'])) : '';
    if (empty($targetUser)) {
        echo json_encode(['status' => 'error']);
        exit;
    }
    
    // Remove request
    $requests = readJson($requestsFile);
    $requests = array_filter($requests, function($req) use ($currentUser, $targetUser) {
        if ((strcasecmp($req['from'], $currentUser) === 0 && strcasecmp($req['to'], $targetUser) === 0) || 
            (strcasecmp($req['from'], $targetUser) === 0 && strcasecmp($req['to'], $currentUser) === 0)) {
            return false;
        }
        return true;
    });
    writeJson($requestsFile, array_values($requests));
    
    echo json_encode(['status' => 'ok']);
    exit;
}

// Action: Delete Account
if ($action === 'delete_account') {
    $requests = readJson($requestsFile);
    $affectedUsers = [];
    $filteredRequests = [];
    
    foreach ($requests as $req) {
        if (strcasecmp($req['from'], $currentUser) === 0) {
            $affectedUsers[] = $req['to'];
        } elseif (strcasecmp($req['to'], $currentUser) === 0) {
            $affectedUsers[] = $req['from'];
        } else {
            $filteredRequests[] = $req;
        }
    }
    writeJson($requestsFile, $filteredRequests);
    
    $affectedUsers = array_values(array_unique($affectedUsers));
    
    // Delete chat files with affected users
    foreach ($affectedUsers as $targetUser) {
        $chatFile = getChatFile($currentUser, $targetUser, $chatsDir);
        if (file_exists($chatFile)) {
            $messages = readJson($chatFile);
            foreach ($messages as $msg) {
                if (!empty($msg['image'])) {
                    $imgPath = __DIR__ . '/' . $msg['image'];
                    if (file_exists($imgPath)) {
                        unlink($imgPath);
                    }
                }
            }
            unlink($chatFile);
        }
    }
    
    // Remove user from users.json
    $users = readJson($usersFile);
    if (isset($users[$currentUser])) {
        unset($users[$currentUser]);
        writeJson($usersFile, $users);
    }
    
    // Destroy session
    session_destroy();
    
    echo json_encode(['status' => 'ok', 'affected' => $affectedUsers]);
    exit;
}

// Action: Rename User
if ($action === 'rename_user') {
    $newUsername = isset($_POST['new_username']) ? strtolower(trim($_POST['new_username'])) : '';
    
    if (empty($newUsername) || strlen($newUsername) > 8 || !preg_match('/^[a-z0-9_]+$/', $newUsername)) {
        echo json_encode(['status' => 'error', 'message' => 'Username must be 1-8 chars (lowercase alphanumeric and underscores).']);
        exit;
    }
    
    if ($newUsername === $currentUser) {
        echo json_encode(['status' => 'error', 'message' => 'That is already your username.']);
        exit;
    }

    $users = readJson($usersFile);
    if (isset($users[$newUsername])) {
        echo json_encode(['status' => 'error', 'message' => 'Username is already taken.']);
        exit;
    }

    // 1. Migrate users.json
    $users[$newUsername] = $users[$currentUser];
    unset($users[$currentUser]);
    writeJson($usersFile, $users);

    // 2. Migrate requests.json
    $requests = readJson($requestsFile);
    $requestsChanged = false;
    foreach ($requests as &$req) {
        if (strcasecmp($req['from'], $currentUser) === 0) {
            $req['from'] = $newUsername;
            $requestsChanged = true;
        }
        if (strcasecmp($req['to'], $currentUser) === 0) {
            $req['to'] = $newUsername;
            $requestsChanged = true;
        }
    }
    if ($requestsChanged) {
        writeJson($requestsFile, $requests);
    }

    // 3. Migrate chat files
    foreach ($users as $otherUser => $data) {
        if ($otherUser === $newUsername) continue;
        
        $oldChatFile = getChatFile($currentUser, $otherUser, $chatsDir);
        if (file_exists($oldChatFile)) {
            $messages = readJson($oldChatFile);
            foreach ($messages as &$msg) {
                if (strcasecmp($msg['user'], $currentUser) === 0) {
                    $msg['user'] = $newUsername;
                }
            }
            $newChatFile = getChatFile($newUsername, $otherUser, $chatsDir);
            // Save to new file
            writeJson($newChatFile, $messages);
            // Delete old file
            unlink($oldChatFile);
        }
    }

    // Update Session
    $_SESSION['username'] = $newUsername;

    echo json_encode(['status' => 'ok']);
    exit;
}

echo json_encode(['error' => 'Invalid action']);

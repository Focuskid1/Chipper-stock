<?php
session_start();

// Simple chat storage (in production, use a database)
$chat_file = __DIR__ . '/../chat_messages.json';

// Initialize chat file if it doesn't exist
if (!file_exists($chat_file)) {
    file_put_contents($chat_file, json_encode([]));
}

// Handle AJAX requests
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    $action = $_GET['action'];
    $messages = json_decode(file_get_contents($chat_file), true);
    
    // Get messages (for clients)
    if ($action == 'get') {
        $last_id = isset($_GET['last_id']) ? intval($_GET['last_id']) : 0;
        $new_messages = array_filter($messages, function($msg) use ($last_id) {
            return $msg['id'] > $last_id;
        });
        echo json_encode(array_values($new_messages));
        exit;
    }
    
    // Send message (from client)
    if ($action == 'send') {
        $data = json_decode(file_get_contents('php://input'), true);
        $message = trim($data['message'] ?? '');
        $name = trim($data['name'] ?? 'Guest');
        $is_admin = isset($data['is_admin']) && $data['is_admin'] === true;
        
        if (empty($message)) {
            echo json_encode(['error' => 'Message cannot be empty']);
            exit;
        }
        
        $messages[] = [
            'id' => count($messages) + 1,
            'name' => htmlspecialchars($name),
            'message' => htmlspecialchars($message),
            'is_admin' => $is_admin,
            'timestamp' => date('Y-m-d H:i:s'),
            'time' => date('h:i A')
        ];
        
        file_put_contents($chat_file, json_encode($messages));
        echo json_encode(['success' => true]);
        exit;
    }
}
?>

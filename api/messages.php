<?php
require_once '../config.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch($action) {
    case 'send':
        sendMessage();
        break;
    case 'list':
        listMessages();
        break;
    default:
        jsonResponse(['success' => false, 'message' => 'Action invalide']);
}

function sendMessage() {
    global $pdo;
    
    if (!isAuthenticated()) {
        jsonResponse(['success' => false, 'message' => 'Non authentifié']);
    }
    
    $user = getCurrentUser();
    $productId = intval($_POST['product_id'] ?? 0);
    $receiverId = intval($_POST['receiver_id'] ?? 0);
    $content = secure($_POST['content'] ?? '');
    
    if (empty($content) || $productId <= 0 || $receiverId <= 0) {
        jsonResponse(['success' => false, 'message' => 'Données invalides']);
    }
    
    $stmt = $pdo->prepare("
        INSERT INTO messages (product_id, sender_id, receiver_id, content, created_at)
        VALUES (?, ?, ?, ?, NOW())
    ");
    
    if ($stmt->execute([$productId, $user['id'], $receiverId, $content])) {
        jsonResponse([
            'success' => true,
            'message' => 'Message envoyé'
        ]);
    } else {
        jsonResponse([
            'success' => false,
            'message' => 'Erreur d\'envoi'
        ]);
    }
}

function listMessages() {
    global $pdo;
    
    if (!isAuthenticated()) {
        jsonResponse(['success' => false, 'message' => 'Non authentifié']);
    }
    
    $user = getCurrentUser();
    
    $stmt = $pdo->prepare("
        SELECT m.*, 
               u1.name as sender_name,
               u2.name as receiver_name,
               p.title as product_title
        FROM messages m
        JOIN users u1 ON m.sender_id = u1.id
        JOIN users u2 ON m.receiver_id = u2.id
        JOIN products p ON m.product_id = p.id
        WHERE m.sender_id = ? OR m.receiver_id = ?
        ORDER BY m.created_at DESC
    ");
    
    $stmt->execute([$user['id'], $user['id']]);
    $messages = $stmt->fetchAll();
    
    jsonResponse([
        'success' => true,
        'messages' => $messages
    ]);
}
?>
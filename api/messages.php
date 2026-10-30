<?php
require_once '../config.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
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

    $user = getCurrentUser();
    if (!$user) {
        http_response_code(401);
        jsonResponse(['success' => false, 'message' => 'Connectez-vous pour envoyer un message']);
    }

    $productId = filter_var($_POST['product_id'] ?? null, FILTER_VALIDATE_INT);
    $requestedReceiverId = filter_var($_POST['receiver_id'] ?? null, FILTER_VALIDATE_INT);
    $content = trim($_POST['content'] ?? '');

    if (!$productId || $productId < 1 || $content === '' || strlen($content) > 5000) {
        http_response_code(400);
        jsonResponse(['success' => false, 'message' => 'Message ou annonce invalide']);
    }

    $stmt = $pdo->prepare("SELECT seller_id FROM products WHERE id = ? AND status = 'active'");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();
    if (!$product) {
        http_response_code(404);
        jsonResponse(['success' => false, 'message' => 'Annonce non disponible']);
    }

    $sellerId = (int) $product['seller_id'];
    $senderId = (int) $user['id'];
    if ($senderId === $sellerId) {
        $stmt = $pdo->prepare("
            SELECT 1 FROM messages
            WHERE product_id = ? AND sender_id = ? AND receiver_id = ?
            LIMIT 1
        ");
        $stmt->execute([$productId, $requestedReceiverId, $sellerId]);
        if (!$requestedReceiverId || !$stmt->fetch()) {
            http_response_code(403);
            jsonResponse(['success' => false, 'message' => 'Vous ne pouvez répondre qu’à une conversation existante']);
        }
        $receiverId = $requestedReceiverId;
    } else {
        $receiverId = $sellerId;
    }

    $stmt = $pdo->prepare("
        INSERT INTO messages (product_id, sender_id, receiver_id, content, created_at)
        VALUES (?, ?, ?, ?, NOW())
    ");
    $stmt->execute([$productId, $senderId, $receiverId, $content]);

    jsonResponse(['success' => true, 'message' => 'Message envoyé']);
}

function listMessages() {
    global $pdo;

    $user = getCurrentUser();
    if (!$user) {
        http_response_code(401);
        jsonResponse(['success' => false, 'message' => 'Connectez-vous pour consulter vos messages']);
    }

    $stmt = $pdo->prepare("
        SELECT m.*, u1.name as sender_name, u2.name as receiver_name, p.title as product_title
        FROM messages m
        JOIN users u1 ON m.sender_id = u1.id
        JOIN users u2 ON m.receiver_id = u2.id
        JOIN products p ON m.product_id = p.id
        WHERE (m.sender_id = ? OR m.receiver_id = ?) AND p.status <> 'deleted'
        ORDER BY m.created_at DESC
    ");
    $stmt->execute([$user['id'], $user['id']]);
    $messages = $stmt->fetchAll();

    jsonResponse(['success' => true, 'messages' => $messages]);
}

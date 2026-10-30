<?php
require_once '../config.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'list':
        listProducts();
        break;
    case 'get':
        getProduct();
        break;
    case 'create':
        createProduct();
        break;
    case 'delete':
        deleteProduct();
        break;
    default:
        jsonResponse(['success' => false, 'message' => 'Action invalide']);
}

function listProducts() {
    global $pdo;

    $stmt = $pdo->query("
        SELECT p.*, u.name as seller_name
        FROM products p
        JOIN users u ON p.seller_id = u.id
        WHERE p.status = 'active'
        ORDER BY p.created_at DESC
    ");
    $products = $stmt->fetchAll();

    foreach ($products as &$product) {
        $product['images'] = json_decode($product['images'], true) ?: [];
        $product['videos'] = json_decode($product['videos'], true) ?: [];
    }

    jsonResponse(['success' => true, 'products' => $products]);
}

function getProduct() {
    global $pdo;

    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if (!$id || $id < 1) {
        http_response_code(400);
        jsonResponse(['success' => false, 'message' => 'Identifiant de produit invalide']);
    }

    $stmt = $pdo->prepare("
        SELECT p.*, u.name as seller_name
        FROM products p
        JOIN users u ON p.seller_id = u.id
        WHERE p.id = ?
    ");
    $stmt->execute([$id]);
    $product = $stmt->fetch();

    if (!$product || ($product['status'] !== 'active' && !isAdmin())) {
        http_response_code(404);
        jsonResponse(['success' => false, 'message' => 'Produit non trouvé']);
    }

    $product['images'] = json_decode($product['images'], true) ?: [];
    $product['videos'] = json_decode($product['videos'], true) ?: [];
    jsonResponse(['success' => true, 'product' => $product]);
}

function createProduct() {
    global $pdo;

    $user = getCurrentUser();
    if (!$user) {
        http_response_code(401);
        jsonResponse(['success' => false, 'message' => 'Connectez-vous pour publier une annonce']);
    }

    $title = trim(strip_tags($_POST['title'] ?? ''));
    $description = trim(strip_tags($_POST['description'] ?? ''));
    $priceInput = trim($_POST['price'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $phone = trim(strip_tags($_POST['phone'] ?? ''));
    $email = strtolower(trim($_POST['email'] ?? ''));
    $categories = [];
    foreach (getProductCategoryGroups() as $group) {
        $categories += $group;
    }

    if ($title === '' || strlen($title) > 255 || $description === ''
        || !is_numeric($priceInput) || (float) $priceInput <= 0
        || (float) $priceInput > 99999999.99
        || !isset($categories[$category]) || $phone === '' || strlen($phone) > 50
        || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        jsonResponse(['success' => false, 'message' => 'Vérifiez les informations de l’annonce']);
    }

    $imageUpload = storeUploadedMedia('images', [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp'
    ], 8, 5 * 1024 * 1024);

    if ($imageUpload['error'] !== null) {
        http_response_code(400);
        jsonResponse(['success' => false, 'message' => $imageUpload['error']]);
    }

    $videoUpload = storeUploadedMedia('videos', [
        'video/mp4' => 'mp4',
        'video/webm' => 'webm',
        'video/quicktime' => 'mov',
        'video/x-msvideo' => 'avi'
    ], 2, 25 * 1024 * 1024);

    if ($videoUpload['error'] !== null) {
        removeUploadedMedia($imageUpload['files']);
        http_response_code(400);
        jsonResponse(['success' => false, 'message' => $videoUpload['error']]);
    }

    $images = $imageUpload['files'];
    $videos = $videoUpload['files'];
    $fee = calculatePublishingFee((float) $priceInput);

    try {
        $stmt = $pdo->prepare("
            INSERT INTO products (
                seller_id, title, description, price, category,
                phone, email, images, videos, status, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', NOW())
        ");
        $stmt->execute([
            $user['id'],
            $title,
            $description,
            $priceInput,
            $category,
            $phone,
            $email,
            json_encode($images),
            json_encode($videos)
        ]);
    } catch (PDOException $e) {
        removeUploadedMedia(array_merge($images, $videos));
        error_log('Product creation failed: ' . $e->getMessage());
        http_response_code(500);
        jsonResponse(['success' => false, 'message' => 'Impossible de publier l’annonce pour le moment']);
    }

    jsonResponse([
        'success' => true,
        'message' => 'Annonce publiée avec succès',
        'fee' => $fee,
        'fee_is_estimate' => true
    ]);
}

function storeUploadedMedia($field, array $allowedTypes, $maxFiles, $maxSize) {
    if (!isset($_FILES[$field])) {
        return ['files' => [], 'error' => null];
    }

    $upload = $_FILES[$field];
    if (!is_array($upload['name']) || !is_array($upload['tmp_name'])
        || !is_array($upload['error']) || !is_array($upload['size'])
        || count($upload['name']) > $maxFiles) {
        return ['files' => [], 'error' => 'Nombre de fichiers invalide'];
    }

    $directory = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads';
    if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
        return ['files' => [], 'error' => 'Le dossier de téléversement est indisponible'];
    }

    $fileInfo = new finfo(FILEINFO_MIME_TYPE);
    $savedFiles = [];

    foreach ($upload['name'] as $index => $originalName) {
        $error = $upload['error'][$index];
        if ($error === UPLOAD_ERR_NO_FILE) {
            continue;
        }

        if ($error !== UPLOAD_ERR_OK || !is_uploaded_file($upload['tmp_name'][$index])) {
            removeUploadedMedia($savedFiles);
            return ['files' => [], 'error' => 'Un fichier n’a pas pu être téléversé'];
        }

        if ($upload['size'][$index] <= 0 || $upload['size'][$index] > $maxSize) {
            removeUploadedMedia($savedFiles);
            return ['files' => [], 'error' => 'La taille d’un fichier dépasse la limite autorisée'];
        }

        $mimeType = $fileInfo->file($upload['tmp_name'][$index]);
        if (!isset($allowedTypes[$mimeType])) {
            removeUploadedMedia($savedFiles);
            return ['files' => [], 'error' => 'Format de fichier non pris en charge'];
        }

        $fileName = bin2hex(random_bytes(16)) . '.' . $allowedTypes[$mimeType];
        if (!move_uploaded_file(
            $upload['tmp_name'][$index],
            $directory . DIRECTORY_SEPARATOR . $fileName
        )) {
            removeUploadedMedia($savedFiles);
            return ['files' => [], 'error' => 'Impossible d’enregistrer un fichier'];
        }

        $savedFiles[] = $fileName;
    }

    return ['files' => $savedFiles, 'error' => null];
}

function removeUploadedMedia(array $files) {
    $directory = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;
    foreach ($files as $file) {
        $path = $directory . basename($file);
        if (is_file($path) && !unlink($path)) {
            error_log('Unable to remove uploaded media file: ' . $path);
        }
    }
}

function deleteProduct() {
    global $pdo;

    if (!isAdmin()) {
        http_response_code(403);
        jsonResponse(['success' => false, 'message' => 'Action réservée à l’administrateur']);
    }

    $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
    if (!$id || $id < 1) {
        http_response_code(400);
        jsonResponse(['success' => false, 'message' => 'Identifiant de produit invalide']);
    }

    $stmt = $pdo->prepare("UPDATE products SET status = 'deleted' WHERE id = ? AND status <> 'deleted'");
    $stmt->execute([$id]);
    if ($stmt->rowCount() === 0) {
        http_response_code(404);
        jsonResponse(['success' => false, 'message' => 'Produit non trouvé']);
    }

    jsonResponse(['success' => true, 'message' => 'Produit supprimé']);
}

function calculatePublishingFee($price) {
    if ($price <= 0) {
        return 0;
    }

    if ($price < 5000) {
        return 150;
    }

    return (int) ceil($price / 5000) * 300;
}

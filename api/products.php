<?php
require_once '../config.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch($action) {
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
    
    // Décoder les images et vidéos JSON
    foreach ($products as &$product) {
        $product['images'] = json_decode($product['images'], true) ?? [];
        $product['videos'] = json_decode($product['videos'], true) ?? [];
    }
    
    jsonResponse([
        'success' => true,
        'products' => $products
    ]);
}

function getProduct() {
    global $pdo;
    
    $id = $_GET['id'] ?? 0;
    
    $stmt = $pdo->prepare("
        SELECT p.*, u.name as seller_name 
        FROM products p 
        JOIN users u ON p.seller_id = u.id 
        WHERE p.id = ?
    ");
    $stmt->execute([$id]);
    $product = $stmt->fetch();
    
    if ($product) {
        $product['images'] = json_decode($product['images'], true) ?? [];
        $product['videos'] = json_decode($product['videos'], true) ?? [];
        
        jsonResponse([
            'success' => true,
            'product' => $product
        ]);
    } else {
        jsonResponse([
            'success' => false,
            'message' => 'Produit non trouvé'
        ]);
    }
}

function createProduct() {
    global $pdo;
    
    if (!isAuthenticated()) {
        jsonResponse(['success' => false, 'message' => 'Non authentifié']);
    }
    
    $user = getCurrentUser();
    
    $title = secure($_POST['title'] ?? '');
    $description = secure($_POST['description'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $category = secure($_POST['category'] ?? '');
    $phone = secure($_POST['phone'] ?? '');
    $email = secure($_POST['email'] ?? '');
    
    if (empty($title) || empty($description) || $price <= 0 || empty($category)) {
        jsonResponse(['success' => false, 'message' => 'Tous les champs sont requis']);
    }
    
    // Calculer les frais
    $fee = calculatePublishingFee($price);
    
    // Upload des images
    $images = [];
    if (isset($_FILES['images'])) {
        $uploadDir = '../' . UPLOAD_DIR;
        
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        foreach ($_FILES['images']['tmp_name'] as $key => $tmpName) {
            if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                $fileName = uniqid() . '_' . basename($_FILES['images']['name'][$key]);
                $targetPath = $uploadDir . $fileName;
                
                if (move_uploaded_file($tmpName, $targetPath)) {
                    $images[] = $fileName;
                }
            }
        }
    }
    
    // Upload des vidéos
    $videos = [];
    if (isset($_FILES['videos'])) {
        $uploadDir = '../' . UPLOAD_DIR;
        
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        foreach ($_FILES['videos']['tmp_name'] as $key => $tmpName) {
            if ($_FILES['videos']['error'][$key] === UPLOAD_ERR_OK) {
                $fileName = uniqid() . '_video_' . basename($_FILES['videos']['name'][$key]);
                $targetPath = $uploadDir . $fileName;
                
                if (move_uploaded_file($tmpName, $targetPath)) {
                    $videos[] = $fileName;
                }
            }
        }
    }
    
    $imagesJson = json_encode($images);
    $videosJson = json_encode($videos);
    
    // Insérer le produit
    $stmt = $pdo->prepare("
        INSERT INTO products (
            seller_id, title, description, price, category, 
            phone, email, images, videos, status, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', NOW())
    ");
    
    if ($stmt->execute([
        $user['id'], $title, $description, $price, 
        $category, $phone, $email, $imagesJson, $videosJson
    ])) {
        // Mettre à jour le solde de l'admin
        $pdo->prepare("UPDATE users SET balance = balance + ? WHERE role = 'admin'")
            ->execute([$fee]);
        
        jsonResponse([
            'success' => true,
            'message' => 'Produit publié avec succès',
            'fee' => $fee
        ]);
    } else {
        jsonResponse([
            'success' => false,
            'message' => 'Erreur lors de la publication'
        ]);
    }
}

function deleteProduct() {
    global $pdo;
    
    if (!isAdmin()) {
        jsonResponse(['success' => false, 'message' => 'Non autorisé']);
    }
    
    $id = $_POST['id'] ?? 0;
    
    $stmt = $pdo->prepare("UPDATE products SET status = 'deleted' WHERE id = ?");
    
    if ($stmt->execute([$id])) {
        jsonResponse([
            'success' => true,
            'message' => 'Produit supprimé'
        ]);
    } else {
        jsonResponse([
            'success' => false,
            'message' => 'Erreur de suppression'
        ]);
    }
}

function calculatePublishingFee($price) {
    if ($price <= 0) return 0;
    
    // Pour les prix inférieurs à 5,000 FCFA : frais fixe de 150 FCFA
    if ($price < 5000) {
        return 150;
    }
    
    // Pour les prix de 5,000 FCFA et plus : 300 FCFA par tranche de 5,000
    $tranches = ceil($price / 5000);
    return $tranches * 300;
}
?>
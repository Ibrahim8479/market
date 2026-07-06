<?php
// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'marketplace');
define('DB_USER', 'root');
define('DB_PASS', '');

// Configuration du site
define('SITE_URL', 'http://localhost/marketplace/');
define('UPLOAD_DIR', 'uploads/');

// Créer la connexion à la base de données
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch(PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Démarrer la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Fonction pour sécuriser les données
function secure($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Fonction pour vérifier l'authentification
function isAuthenticated() {
    return isset($_SESSION['user_id']);
}

// Fonction pour obtenir l'utilisateur connecté
function getCurrentUser() {
    global $pdo;
    
    if (!isAuthenticated()) {
        return null;
    }
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

// Fonction pour vérifier si l'utilisateur est admin
function isAdmin() {
    $user = getCurrentUser();
    return $user && $user['role'] === 'admin';
}

// Headers JSON
function jsonResponse($data) {
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}
?>
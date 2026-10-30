<?php
// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'marketplace');
define('DB_USER', 'root');
define('DB_PASS', '');

// Configuration du site
define('SITE_URL', 'http://localhost/market/');
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
    error_log('Database connection failed: ' . $e->getMessage());
    http_response_code(503);

    if (isset($_SERVER['SCRIPT_NAME']) && strpos($_SERVER['SCRIPT_NAME'], '/api/') !== false) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => 'Service temporairement indisponible']);
    } else {
        echo 'Service temporairement indisponible. Vérifiez la configuration de la base de données.';
    }

    exit;
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
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

function getProductCategoryGroups($includeLegacy = false) {
    $groups = [
        'Électronique & High-Tech' => [
            'smartphones' => 'Smartphones & Tablettes',
            'ordinateurs' => 'Ordinateurs & Laptops',
            'tv-audio' => 'TV, Audio & Photo',
            'accessoires-tech' => 'Accessoires High-Tech',
            'jeux-video' => 'Jeux Vidéo & Consoles'
        ],
        'Mode & Beauté' => [
            'vetements-hommes' => 'Vêtements Hommes',
            'vetements-femmes' => 'Vêtements Femmes',
            'vetements-enfants' => 'Vêtements Enfants',
            'chaussures' => 'Chaussures',
            'sacs-accessoires' => 'Sacs & Accessoires',
            'montres-bijoux' => 'Montres & Bijoux',
            'beaute-parfums' => 'Beauté & Parfums'
        ],
        'Maison & Jardin' => [
            'meubles' => 'Meubles',
            'electromenager' => 'Électroménager',
            'decoration' => 'Décoration',
            'cuisine' => 'Articles de Cuisine',
            'jardin' => 'Jardin & Extérieur',
            'bricolage' => 'Bricolage & Outils'
        ],
        'Véhicules' => [
            'voitures' => 'Voitures',
            'motos' => 'Motos & Scooters',
            'pieces-auto' => 'Pièces Auto & Moto',
            'velos' => 'Vélos'
        ],
        'Loisirs & Divertissement' => [
            'sport-fitness' => 'Sport & Fitness',
            'livres' => 'Livres & Magazines',
            'musique' => 'Instruments de Musique',
            'jouets' => 'Jouets & Jeux',
            'collection' => 'Collection & Art'
        ],
        'Services & Emploi' => [
            'services-pro' => 'Services Professionnels',
            'cours' => 'Cours & Formations',
            'emploi' => 'Offres d’Emploi',
            'evenements' => 'Événements'
        ],
        'Immobilier' => [
            'appartements' => 'Appartements',
            'maisons' => 'Maisons & Villas',
            'terrains' => 'Terrains',
            'bureaux' => 'Bureaux & Commerces'
        ],
        'Animaux' => [
            'chiens-chats' => 'Chiens & Chats',
            'autres-animaux' => 'Autres Animaux',
            'accessoires-animaux' => 'Accessoires pour Animaux'
        ],
        'Autres' => [
            'alimentation' => 'Alimentation & Boissons',
            'bebe-puericulture' => 'Bébé & Puériculture',
            'sante' => 'Santé & Bien-être',
            'autres' => 'Autres'
        ]
    ];

    if ($includeLegacy) {
        $groups['Anciennes catégories'] = [
            'electronics' => 'Électronique',
            'fashion' => 'Mode',
            'home' => 'Maison',
            'vehicles' => 'Véhicules',
            'services' => 'Services',
            'other' => 'Autre'
        ];
    }

    return $groups;
}
?>
<?php
require_once '../config.php';

// Vérifier l'action
$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch($action) {
    case 'check':
        checkAuth();
        break;
    case 'login':
        login();
        break;
    case 'signup':
        signup();
        break;
    case 'logout':
        logout();
        break;
    default:
        jsonResponse(['success' => false, 'message' => 'Action invalide']);
}

function checkAuth() {
    $user = getCurrentUser();
    
    if ($user) {
        unset($user['password']);
        jsonResponse([
            'success' => true,
            'user' => $user
        ]);
    } else {
        jsonResponse([
            'success' => false,
            'user' => null
        ]);
    }
}

function login() {
    global $pdo;
    
    $email = secure($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        jsonResponse(['success' => false, 'message' => 'Email et mot de passe requis']);
    }
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        unset($user['password']);
        
        jsonResponse([
            'success' => true,
            'message' => 'Connexion réussie',
            'user' => $user
        ]);
    } else {
        jsonResponse([
            'success' => false,
            'message' => 'Email ou mot de passe incorrect'
        ]);
    }
}

function signup() {
    global $pdo;
    
    $name = secure($_POST['name'] ?? '');
    $email = secure($_POST['email'] ?? '');
    $phone = secure($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($name) || empty($email) || empty($phone) || empty($password)) {
        jsonResponse(['success' => false, 'message' => 'Tous les champs sont requis']);
    }
    
    // Vérifier si l'email existe déjà
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->fetch()) {
        jsonResponse(['success' => false, 'message' => 'Email déjà utilisé']);
    }
    
    // Créer l'utilisateur
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("
        INSERT INTO users (name, email, phone, password, role, balance, created_at) 
        VALUES (?, ?, ?, ?, 'user', 0, NOW())
    ");
    
    if ($stmt->execute([$name, $email, $phone, $hashedPassword])) {
        $userId = $pdo->lastInsertId();
        $_SESSION['user_id'] = $userId;
        
        $user = [
            'id' => $userId,
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'role' => 'user',
            'balance' => 0
        ];
        
        jsonResponse([
            'success' => true,
            'message' => 'Inscription réussie',
            'user' => $user
        ]);
    } else {
        jsonResponse(['success' => false, 'message' => 'Erreur lors de l\'inscription']);
    }
}

function logout() {
    session_destroy();
    jsonResponse(['success' => true, 'message' => 'Déconnexion réussie']);
}
?>
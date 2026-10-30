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

    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
        jsonResponse(['success' => false, 'message' => 'Email et mot de passe requis']);
    }

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        session_regenerate_id(true);
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

    $name = trim(strip_tags($_POST['name'] ?? ''));
    $email = strtolower(trim($_POST['email'] ?? ''));
    $phone = trim(strip_tags($_POST['phone'] ?? ''));
    $password = $_POST['password'] ?? '';

    if ($name === '' || strlen($name) > 255 || !filter_var($email, FILTER_VALIDATE_EMAIL)
        || $phone === '' || strlen($phone) > 50 || strlen($password) < 8) {
        jsonResponse([
            'success' => false,
            'message' => 'Vérifiez les informations saisies. Le mot de passe doit contenir au moins 8 caractères.'
        ]);
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("
        INSERT INTO users (name, email, phone, password, role, balance, created_at) 
        VALUES (?, ?, ?, ?, 'user', 0, NOW())
    ");

    try {
        $stmt->execute([$name, $email, $phone, $hashedPassword]);
    } catch (PDOException $e) {
        if ($e->getCode() === '23000') {
            jsonResponse(['success' => false, 'message' => 'Email déjà utilisé']);
        }

        error_log('Account creation failed: ' . $e->getMessage());
        http_response_code(500);
        jsonResponse(['success' => false, 'message' => 'Erreur lors de l’inscription']);
    }

    $userId = $pdo->lastInsertId();
    session_regenerate_id(true);
    $_SESSION['user_id'] = $userId;

    jsonResponse([
        'success' => true,
        'message' => 'Inscription réussie',
        'user' => [
            'id' => $userId,
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'role' => 'user',
            'balance' => 0
        ]
    ]);
}

function logout() {
    $_SESSION = [];
    session_destroy();
    jsonResponse(['success' => true, 'message' => 'Déconnexion réussie']);
}
?>
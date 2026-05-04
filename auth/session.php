<?php
/**
 * Gestion des sessions et authentification
 */

require_once __DIR__ . '/../config/config.php';

if (defined('SESSION_PHP_INCLUDED')) {
    return;
}
define('SESSION_PHP_INCLUDED', true);

// Démarrer la session si elle n'est pas déjà active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Vérifier si l'utilisateur est connecté
 */
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Vérifier si l'utilisateur a un rôle spécifique
 */
function has_role($required_role) {
    if (!is_logged_in()) {
        return false;
    }
    
    $user_role = $_SESSION['user_role'] ?? null;
    
    // Hiérarchie des rôles
    $role_hierarchy = [
        ROLE_CAISSIER => 1,
        ROLE_MANAGER => 2,
        ROLE_ADMIN => 3
    ];
    
    $user_level = $role_hierarchy[$user_role] ?? 0;
    $required_level = $role_hierarchy[$required_role] ?? 0;
    
    return $user_level >= $required_level;
}

/**
 * Rediriger vers la page de connexion si non authentifié
 */
function require_login() {
    if (!is_logged_in()) {
        header('Location: ' . get_base_url() . 'auth/login.php');
        exit();
    }
}

/**
 * Rediriger si le rôle n'est pas suffisant
 */
function require_role($required_role) {
    require_login();
    
    if (!has_role($required_role)) {
        $_SESSION['error'] = 'Accès refusé. Rôle insuffisant.';
        header('Location: ' . get_base_url() . 'index.php');
        exit();
    }
}

/**
 * Obtenir l'URL de base de l'application
 */
function get_base_url() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $path = dirname($_SERVER['SCRIPT_NAME']);
    
    return $protocol . '://' . $host . $path . '/';
}

/**
 * Obtenir les informations de l'utilisateur connecté
 */
function get_authenticated_user() {
    if (!is_logged_in()) {
        return null;
    }
    
    $users = json_decode(file_get_contents(UTILISATEURS_FILE), true) ?? [];
    
    foreach ($users as $user) {
        if ($user['identifiant'] === $_SESSION['user_id']) {
            return $user;
        }
    }
    
    return null;
}

/**
 * Déconnecter l'utilisateur
 */
function logout() {
    session_destroy();
    header('Location: ' . get_base_url() . 'auth/login.php');
    exit();
}
?>

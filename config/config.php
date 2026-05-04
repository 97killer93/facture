<?php
/**
 * Configuration globale du système de facturation
 */

// Paramètres de l'application
define('APP_NAME', 'Système de Facturation UPC');
define('APP_VERSION', '1.0.0');

// Chemins des fichiers de données
define('DATA_DIR', __DIR__ . '/../data/');
define('PRODUITS_FILE', DATA_DIR . 'produits.json');
define('FACTURES_FILE', DATA_DIR . 'factures.json');
define('UTILISATEURS_FILE', DATA_DIR . 'utilisateurs.json');

// Paramètres fiscaux
define('TVA_RATE', 0.18); // 18% de TVA

// Paramètres de session
define('SESSION_TIMEOUT', 3600); // 1 heure en secondes

// Rôles utilisateur
define('ROLE_CAISSIER', 'caissier');
define('ROLE_MANAGER', 'manager');
define('ROLE_ADMIN', 'super_admin');

// Initialiser les fichiers de données s'ils n'existent pas
if (!file_exists(DATA_DIR)) {
    mkdir(DATA_DIR, 0755, true);
}

if (!file_exists(PRODUITS_FILE)) {
    file_put_contents(PRODUITS_FILE, json_encode([], JSON_PRETTY_PRINT));
}

if (!file_exists(FACTURES_FILE)) {
    file_put_contents(FACTURES_FILE, json_encode([], JSON_PRETTY_PRINT));
}

if (!file_exists(UTILISATEURS_FILE)) {
    // Créer un compte super administrateur par défaut
    $admin_initial = [
        [
            'identifiant' => 'admin',
            'mot_de_passe' => password_hash('Admin@2026', PASSWORD_BCRYPT),
            'role' => ROLE_ADMIN,
            'nom_complet' => 'Administrateur Système',
            'date_creation' => date('Y-m-d'),
            'actif' => true
        ]
    ];
    file_put_contents(UTILISATEURS_FILE, json_encode($admin_initial, JSON_PRETTY_PRINT));
}
?>

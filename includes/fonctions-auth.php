<?php
/**
 * Fonctions de gestion de l'authentification et des utilisateurs
 */

require_once __DIR__ . '/../config/config.php';

/**
 * Charger tous les utilisateurs
 */
function charger_utilisateurs() {
    if (!file_exists(UTILISATEURS_FILE)) {
        return [];
    }
    
    $content = file_get_contents(UTILISATEURS_FILE);
    return json_decode($content, true) ?? [];
}

/**
 * Sauvegarder les utilisateurs
 */
function sauvegarder_utilisateurs($utilisateurs) {
    return file_put_contents(UTILISATEURS_FILE, json_encode($utilisateurs, JSON_PRETTY_PRINT)) !== false;
}

/**
 * Chercher un utilisateur par identifiant
 */
function chercher_utilisateur($identifiant) {
    $utilisateurs = charger_utilisateurs();
    
    foreach ($utilisateurs as $user) {
        if ($user['identifiant'] === $identifiant) {
            return $user;
        }
    }
    
    return null;
}

/**
 * Ajouter un nouvel utilisateur
 */
function ajouter_utilisateur($identifiant, $mot_de_passe, $role, $nom_complet) {
    // Validation
    $erreurs = valider_utilisateur($identifiant, $mot_de_passe, $role, $nom_complet);
    
    if (!empty($erreurs)) {
        return ['success' => false, 'erreurs' => $erreurs];
    }
    
    // Vérifier si l'utilisateur existe déjà
    if (chercher_utilisateur($identifiant)) {
        return ['success' => false, 'erreurs' => ['L\'identifiant existe déjà.']];
    }
    
    // Créer le nouvel utilisateur
    $utilisateur = [
        'identifiant' => $identifiant,
        'mot_de_passe' => password_hash($mot_de_passe, PASSWORD_BCRYPT),
        'role' => $role,
        'nom_complet' => $nom_complet,
        'date_creation' => date('Y-m-d'),
        'actif' => true
    ];
    
    // Ajouter à la liste
    $utilisateurs = charger_utilisateurs();
    $utilisateurs[] = $utilisateur;
    
    // Sauvegarder
    if (sauvegarder_utilisateurs($utilisateurs)) {
        return ['success' => true, 'utilisateur' => $utilisateur];
    } else {
        return ['success' => false, 'erreurs' => ['Erreur lors de la sauvegarde.']];
    }
}

/**
 * Valider les données d'un utilisateur
 */
function valider_utilisateur($identifiant, $mot_de_passe, $role, $nom_complet) {
    $erreurs = [];
    
    if (empty($identifiant)) {
        $erreurs[] = 'L\'identifiant est requis.';
    } elseif (strlen($identifiant) < 3) {
        $erreurs[] = 'L\'identifiant doit contenir au moins 3 caractères.';
    }
    
    if (empty($mot_de_passe)) {
        $erreurs[] = 'Le mot de passe est requis.';
    } elseif (strlen($mot_de_passe) < 6) {
        $erreurs[] = 'Le mot de passe doit contenir au moins 6 caractères.';
    }
    
    if (empty($role) || !in_array($role, [ROLE_CAISSIER, ROLE_MANAGER, ROLE_ADMIN])) {
        $erreurs[] = 'Le rôle est invalide.';
    }
    
    if (empty($nom_complet)) {
        $erreurs[] = 'Le nom complet est requis.';
    }
    
    return $erreurs;
}

/**
 * Supprimer un utilisateur
 */
function supprimer_utilisateur($identifiant) {
    $utilisateurs = charger_utilisateurs();
    
    foreach ($utilisateurs as $key => $user) {
        if ($user['identifiant'] === $identifiant) {
            unset($utilisateurs[$key]);
            break;
        }
    }
    
    return sauvegarder_utilisateurs(array_values($utilisateurs));
}

/**
 * Désactiver un utilisateur
 */
function desactiver_utilisateur($identifiant) {
    $utilisateurs = charger_utilisateurs();
    
    foreach ($utilisateurs as &$user) {
        if ($user['identifiant'] === $identifiant) {
            $user['actif'] = false;
            break;
        }
    }
    
    return sauvegarder_utilisateurs($utilisateurs);
}

/**
 * Obtenir tous les utilisateurs
 */
function obtenir_tous_utilisateurs() {
    return charger_utilisateurs();
}

/**
 * Obtenir les utilisateurs par rôle
 */
function obtenir_utilisateurs_par_role($role) {
    $utilisateurs = charger_utilisateurs();
    $result = [];
    
    foreach ($utilisateurs as $user) {
        if ($user['role'] === $role) {
            $result[] = $user;
        }
    }
    
    return $result;
}
?>

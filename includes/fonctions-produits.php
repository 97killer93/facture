<?php
/**
 * Fonctions de gestion des produits
 */

require_once __DIR__ . '/../config/config.php';

/**
 * Charger tous les produits
 */
function charger_produits() {
    if (!file_exists(PRODUITS_FILE)) {
        return [];
    }
    
    $content = file_get_contents(PRODUITS_FILE);
    return json_decode($content, true) ?? [];
}

/**
 * Sauvegarder les produits
 */
function sauvegarder_produits($produits) {
    return file_put_contents(PRODUITS_FILE, json_encode($produits, JSON_PRETTY_PRINT)) !== false;
}

/**
 * Chercher un produit par code-barres
 */
function chercher_produit_par_code($code_barre) {
    $produits = charger_produits();
    
    foreach ($produits as $produit) {
        if ($produit['code_barre'] === $code_barre) {
            return $produit;
        }
    }
    
    return null;
}

/**
 * Ajouter un nouveau produit
 */
function ajouter_produit($code_barre, $nom, $prix_unitaire_ht, $date_expiration, $quantite_stock) {
    // Validation
    $erreurs = valider_produit($code_barre, $nom, $prix_unitaire_ht, $date_expiration, $quantite_stock);
    
    if (!empty($erreurs)) {
        return ['success' => false, 'erreurs' => $erreurs];
    }
    
    // Vérifier si le produit existe déjà
    if (chercher_produit_par_code($code_barre)) {
        return ['success' => false, 'erreurs' => ['Le code-barres existe déjà.']];
    }
    
    // Créer le nouveau produit
    $produit = [
        'code_barre' => $code_barre,
        'nom' => $nom,
        'prix_unitaire_ht' => (int)$prix_unitaire_ht,
        'date_expiration' => $date_expiration,
        'quantite_stock' => (int)$quantite_stock,
        'date_enregistrement' => date('Y-m-d')
    ];
    
    // Ajouter à la liste
    $produits = charger_produits();
    $produits[] = $produit;
    
    // Sauvegarder
    if (sauvegarder_produits($produits)) {
        return ['success' => true, 'produit' => $produit];
    } else {
        return ['success' => false, 'erreurs' => ['Erreur lors de la sauvegarde.']];
    }
}

/**
 * Valider les données d'un produit
 */
function valider_produit($code_barre, $nom, $prix_unitaire_ht, $date_expiration, $quantite_stock) {
    $erreurs = [];
    
    // Vérifier les champs vides
    if (empty($code_barre)) {
        $erreurs[] = 'Le code-barres est requis.';
    }
    
    if (empty($nom)) {
        $erreurs[] = 'Le nom du produit est requis.';
    }
    
    if (empty($prix_unitaire_ht)) {
        $erreurs[] = 'Le prix unitaire HT est requis.';
    } elseif (!is_numeric($prix_unitaire_ht) || $prix_unitaire_ht <= 0) {
        $erreurs[] = 'Le prix unitaire HT doit être un nombre positif.';
    }
    
    if (empty($date_expiration)) {
        $erreurs[] = 'La date d\'expiration est requise.';
    } elseif (!valider_date($date_expiration)) {
        $erreurs[] = 'La date d\'expiration doit être au format MM-JJ-AAAA.';
    }
    
    if (empty($quantite_stock)) {
        $erreurs[] = 'La quantité en stock est requise.';
    } elseif (!is_numeric($quantite_stock) || $quantite_stock < 0) {
        $erreurs[] = 'La quantité en stock doit être un nombre positif.';
    }
    
    return $erreurs;
}

/**
 * Valider une date au format MM-JJ-AAAA
 */
function valider_date($date) {
    $d = DateTime::createFromFormat('m-d-Y', $date);
    return $d && $d->format('m-d-Y') === $date;
}

/**
 * Mettre à jour le stock d'un produit
 */
function mettre_a_jour_stock($code_barre, $quantite_vendue) {
    $produits = charger_produits();
    
    foreach ($produits as &$produit) {
        if ($produit['code_barre'] === $code_barre) {
            $produit['quantite_stock'] -= $quantite_vendue;
            break;
        }
    }
    
    return sauvegarder_produits($produits);
}

/**
 * Obtenir tous les produits
 */
function obtenir_tous_produits() {
    return charger_produits();
}
?>

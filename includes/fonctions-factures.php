<?php
/**
 * Fonctions de gestion des factures
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/fonctions-produits.php';

/**
 * Charger toutes les factures
 */
function charger_factures() {
    if (!file_exists(FACTURES_FILE)) {
        return [];
    }
    
    $content = file_get_contents(FACTURES_FILE);
    return json_decode($content, true) ?? [];
}

/**
 * Sauvegarder les factures
 */
function sauvegarder_factures($factures) {
    return file_put_contents(FACTURES_FILE, json_encode($factures, JSON_PRETTY_PRINT)) !== false;
}

/**
 * Générer un identifiant de facture unique
 */
function generer_id_facture() {
    $factures = charger_factures();
    $date = date('Ymd');
    
    // Compter les factures du jour
    $count = 0;
    foreach ($factures as $facture) {
        if (strpos($facture['id_facture'], $date) !== false) {
            $count++;
        }
    }
    
    return 'FAC-' . $date . '-' . str_pad($count + 1, 3, '0', STR_PAD_LEFT);
}

/**
 * Créer une nouvelle facture
 */
function creer_facture($articles, $caissier) {
    if (empty($articles)) {
        return ['success' => false, 'erreur' => 'Aucun article dans la facture.'];
    }
    
    // Calculer les totaux
    $total_ht = 0;
    foreach ($articles as $article) {
        $total_ht += $article['sous_total_ht'];
    }
    
    $tva = round($total_ht * TVA_RATE);
    $total_ttc = $total_ht + $tva;
    
    // Créer la facture
    $facture = [
        'id_facture' => generer_id_facture(),
        'date' => date('Y-m-d'),
        'heure' => date('H:i:s'),
        'caissier' => $caissier,
        'articles' => $articles,
        'total_ht' => $total_ht,
        'tva' => $tva,
        'total_ttc' => $total_ttc
    ];
    
    // Sauvegarder
    $factures = charger_factures();
    $factures[] = $facture;
    
    if (sauvegarder_factures($factures)) {
        // Mettre à jour le stock
        foreach ($articles as $article) {
            mettre_a_jour_stock($article['code_barre'], $article['quantite']);
        }
        
        return ['success' => true, 'facture' => $facture];
    } else {
        return ['success' => false, 'erreur' => 'Erreur lors de la sauvegarde de la facture.'];
    }
}

/**
 * Obtenir une facture par ID
 */
function obtenir_facture($id_facture) {
    $factures = charger_factures();
    
    foreach ($factures as $facture) {
        if ($facture['id_facture'] === $id_facture) {
            return $facture;
        }
    }
    
    return null;
}

/**
 * Formater une facture pour l'affichage
 */
function formater_facture($facture) {
    $html = '<div class="facture-container">';
    $html .= '<div class="facture-header">';
    $html .= '<h2>Facture #' . htmlspecialchars($facture['id_facture']) . '</h2>';
    $html .= '<p>Date: ' . htmlspecialchars($facture['date']) . ' ' . htmlspecialchars($facture['heure']) . '</p>';
    $html .= '<p>Caissier: ' . htmlspecialchars($facture['caissier']) . '</p>';
    $html .= '</div>';
    
    $html .= '<table class="facture-table">';
    $html .= '<thead>';
    $html .= '<tr>';
    $html .= '<th>Désignation</th>';
    $html .= '<th>Prix unit. HT</th>';
    $html .= '<th>Qté</th>';
    $html .= '<th>Sous-total HT</th>';
    $html .= '</tr>';
    $html .= '</thead>';
    $html .= '<tbody>';
    
    foreach ($facture['articles'] as $article) {
        $html .= '<tr>';
        $html .= '<td>' . htmlspecialchars($article['nom']) . '</td>';
        $html .= '<td>' . number_format($article['prix_unitaire_ht'], 0, ',', ' ') . ' CDF</td>';
        $html .= '<td>' . htmlspecialchars($article['quantite']) . '</td>';
        $html .= '<td>' . number_format($article['sous_total_ht'], 0, ',', ' ') . ' CDF</td>';
        $html .= '</tr>';
    }
    
    $html .= '</tbody>';
    $html .= '</table>';
    
    $html .= '<div class="facture-totals">';
    $html .= '<p><strong>Total HT:</strong> ' . number_format($facture['total_ht'], 0, ',', ' ') . ' CDF</p>';
    $html .= '<p><strong>TVA (18%):</strong> ' . number_format($facture['tva'], 0, ',', ' ') . ' CDF</p>';
    $html .= '<p class="total-ttc"><strong>Net à payer:</strong> ' . number_format($facture['total_ttc'], 0, ',', ' ') . ' CDF</p>';
    $html .= '</div>';
    
    $html .= '</div>';
    
    return $html;
}

/**
 * Obtenir les factures d'une date spécifique
 */
function obtenir_factures_par_date($date) {
    $factures = charger_factures();
    $result = [];
    
    foreach ($factures as $facture) {
        if ($facture['date'] === $date) {
            $result[] = $facture;
        }
    }
    
    return $result;
}

/**
 * Calculer les statistiques journalières
 */
function calculer_stats_journalieres($date) {
    $factures = obtenir_factures_par_date($date);
    
    $stats = [
        'nombre_factures' => count($factures),
        'total_ht' => 0,
        'total_tva' => 0,
        'total_ttc' => 0
    ];
    
    foreach ($factures as $facture) {
        $stats['total_ht'] += $facture['total_ht'];
        $stats['total_tva'] += $facture['tva'];
        $stats['total_ttc'] += $facture['total_ttc'];
    }
    
    return $stats;
}
?>

<?php
/**
 * Module de création de nouvelle facture
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../includes/fonctions-produits.php';
require_once __DIR__ . '/../../includes/fonctions-factures.php';

require_role(ROLE_CAISSIER);

$page_title = 'Nouvelle facture';
$articles = [];
$erreur = '';
$succes = '';

// Traiter l'ajout d'article
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'ajouter_article') {
        $code_barre = trim($_POST['code_barre'] ?? '');
        $quantite = intval($_POST['quantite'] ?? 0);
        
        if (empty($code_barre)) {
            $erreur = 'Veuillez entrer un code-barres.';
        } elseif ($quantite <= 0) {
            $erreur = 'La quantité doit être supérieure à 0.';
        } else {
            $produit = chercher_produit_par_code($code_barre);
            
            if (!$produit) {
                $erreur = 'Produit non trouvé. Veuillez le faire enregistrer par un Manager.';
            } elseif ($produit['quantite_stock'] < $quantite) {
                $erreur = 'Stock insuffisant. Stock disponible : ' . $produit['quantite_stock'];
            } else {
                // Ajouter l'article à la facture
                $_SESSION['facture_articles'] = $_SESSION['facture_articles'] ?? [];
                
                $article = [
                    'code_barre' => $produit['code_barre'],
                    'nom' => $produit['nom'],
                    'prix_unitaire_ht' => $produit['prix_unitaire_ht'],
                    'quantite' => $quantite,
                    'sous_total_ht' => $produit['prix_unitaire_ht'] * $quantite
                ];
                
                $_SESSION['facture_articles'][] = $article;
                $succes = 'Article ajouté à la facture.';
            }
        }
    } elseif ($_POST['action'] === 'valider_facture') {
        $articles = $_SESSION['facture_articles'] ?? [];
        
        if (empty($articles)) {
            $erreur = 'La facture doit contenir au moins un article.';
        } else {
            $result = creer_facture($articles, $_SESSION['user_id']);
            
            if ($result['success']) {
                $_SESSION['facture_articles'] = [];
                $_SESSION['derniere_facture_id'] = $result['facture']['id_facture'];
                header('Location: ' . get_base_url() . 'modules/facturation/afficher-facture.php?id=' . $result['facture']['id_facture']);
                exit();
            } else {
                $erreur = $result['erreur'] ?? 'Erreur lors de la création de la facture.';
            }
        }
    } elseif ($_POST['action'] === 'supprimer_article') {
        $index = intval($_POST['index'] ?? -1);
        
        if ($index >= 0 && isset($_SESSION['facture_articles'][$index])) {
            unset($_SESSION['facture_articles'][$index]);
            $_SESSION['facture_articles'] = array_values($_SESSION['facture_articles']);
            $succes = 'Article supprimé.';
        }
    } elseif ($_POST['action'] === 'reinitialiser') {
        $_SESSION['facture_articles'] = [];
        $succes = 'Facture réinitialisée.';
    }
}

$articles = $_SESSION['facture_articles'] ?? [];

// Calculer les totaux
$total_ht = 0;
foreach ($articles as $article) {
    $total_ht += $article['sous_total_ht'];
}
$tva = round($total_ht * TVA_RATE);
$total_ttc = $total_ht + $tva;

include __DIR__ . '/../../includes/header.php';
?>

<div class="container">
    <h2>Créer une nouvelle facture</h2>
    
    <?php if (!empty($erreur)): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($erreur); ?></div>
    <?php endif; ?>
    
    <?php if (!empty($succes)): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($succes); ?></div>
    <?php endif; ?>
    
    <!-- Section scanner -->
    <div class="form-section">
        <h3>Ajouter des articles</h3>
        <form method="POST" class="form-inline">
            <input type="hidden" name="action" value="ajouter_article">
            
            <div class="form-field">
                <label for="code_barre">Code-barres</label>
                <input type="text" id="code_barre" name="code_barre" placeholder="Scannez le code-barres" autofocus>
            </div>
            
            <div class="form-field">
                <label for="quantite">Quantité</label>
                <input type="number" id="quantite" name="quantite" min="1" value="1">
            </div>
            
            <button type="submit" class="btn btn-primary">Ajouter</button>
        </form>
    </div>
    
    <!-- Tableau des articles -->
    <div class="form-section">
        <h3>Articles de la facture</h3>
        
        <?php if (empty($articles)): ?>
            <p class="no-data">Aucun article pour le moment.</p>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Désignation</th>
                        <th>Prix unit. HT</th>
                        <th>Qté</th>
                        <th>Sous-total HT</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($articles as $index => $article): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($article['nom']); ?></td>
                            <td><?php echo number_format($article['prix_unitaire_ht'], 0, ',', ' '); ?> CDF</td>
                            <td><?php echo htmlspecialchars($article['quantite']); ?></td>
                            <td><?php echo number_format($article['sous_total_ht'], 0, ',', ' '); ?> CDF</td>
                            <td>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="action" value="supprimer_article">
                                    <input type="hidden" name="index" value="<?php echo $index; ?>">
                                    <button type="submit" class="btn btn-danger btn-small">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    
    <!-- Récapitulatif -->
    <?php if (!empty($articles)): ?>
        <div class="facture-recap">
            <div class="recap-row">
                <span>Total HT :</span>
                <strong><?php echo number_format($total_ht, 0, ',', ' '); ?> CDF</strong>
            </div>
            <div class="recap-row">
                <span>TVA (18%) :</span>
                <strong><?php echo number_format($tva, 0, ',', ' '); ?> CDF</strong>
            </div>
            <div class="recap-row total">
                <span>Net à payer :</span>
                <strong><?php echo number_format($total_ttc, 0, ',', ' '); ?> CDF</strong>
            </div>
        </div>
        
        <div class="form-actions">
            <form method="POST" style="display:inline;">
                <input type="hidden" name="action" value="valider_facture">
                <button type="submit" class="btn btn-success">Valider la facture</button>
            </form>
            
            <form method="POST" style="display:inline;">
                <input type="hidden" name="action" value="reinitialiser">
                <button type="submit" class="btn btn-secondary">Réinitialiser</button>
            </form>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>

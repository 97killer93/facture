<?php
/**
 * Liste des produits
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../includes/fonctions-produits.php';

require_role(ROLE_MANAGER);

$page_title = 'Catalogue des produits';
$produits = obtenir_tous_produits();

include __DIR__ . '/../../includes/header.php';
?>

<div class="container">
    <h2>Catalogue des produits</h2>
    
    <div class="table-section">
        <?php if (empty($produits)): ?>
            <p class="no-data">Aucun produit enregistré pour le moment.</p>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Code-barres</th>
                        <th>Nom</th>
                        <th>Prix HT (CDF)</th>
                        <th>Date d'expiration</th>
                        <th>Stock</th>
                        <th>Date d'enregistrement</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($produits as $produit): ?>
                        <tr>
                            <td><code><?php echo htmlspecialchars($produit['code_barre']); ?></code></td>
                            <td><?php echo htmlspecialchars($produit['nom']); ?></td>
                            <td><?php echo number_format($produit['prix_unitaire_ht'], 0, ',', ' '); ?></td>
                            <td><?php echo htmlspecialchars($produit['date_expiration']); ?></td>
                            <td>
                                <span class="stock-badge <?php echo $produit['quantite_stock'] > 0 ? 'stock-ok' : 'stock-faible'; ?>">
                                    <?php echo htmlspecialchars($produit['quantite_stock']); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($produit['date_enregistrement']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    
    <div class="action-buttons">
        <a href="<?php echo get_base_url(); ?>modules/produits/enregistrer.php" class="btn btn-primary">Ajouter un produit</a>
        <a href="<?php echo get_base_url(); ?>index.php" class="btn btn-secondary">Retour</a>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>

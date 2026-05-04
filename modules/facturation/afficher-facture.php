<?php
/**
 * Affichage des factures
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../includes/fonctions-factures.php';

require_role(ROLE_CAISSIER);

$page_title = 'Mes factures';
$facture = null;
$factures = charger_factures();

// Filtrer les factures du caissier
$mes_factures = [];
foreach ($factures as $f) {
    if ($f['caissier'] === $_SESSION['user_id']) {
        $mes_factures[] = $f;
    }
}

// Afficher une facture spécifique si demandé
$id_facture = $_GET['id'] ?? null;
if ($id_facture) {
    foreach ($mes_factures as $f) {
        if ($f['id_facture'] === $id_facture) {
            $facture = $f;
            break;
        }
    }
}

include __DIR__ . '/../../includes/header.php';
?>

<div class="container">
    <h2>Mes factures</h2>
    
    <?php if ($facture): ?>
        <!-- Affichage d'une facture spécifique -->
        <div class="form-section">
            <a href="<?php echo get_base_url(); ?>modules/facturation/afficher-facture.php" class="btn btn-secondary">← Retour à la liste</a>
            
            <?php echo formater_facture($facture); ?>
            
            <div class="form-actions">
                <button onclick="window.print();" class="btn btn-primary">Imprimer</button>
                <a href="<?php echo get_base_url(); ?>modules/facturation/afficher-facture.php" class="btn btn-secondary">Retour</a>
            </div>
        </div>
    <?php else: ?>
        <!-- Liste des factures -->
        <div class="table-section">
            <?php if (empty($mes_factures)): ?>
                <p class="no-data">Vous n'avez pas encore créé de facture.</p>
            <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Numéro</th>
                            <th>Date</th>
                            <th>Heure</th>
                            <th>Articles</th>
                            <th>Total HT</th>
                            <th>Total TTC</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_reverse($mes_factures) as $f): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($f['id_facture']); ?></strong></td>
                                <td><?php echo htmlspecialchars($f['date']); ?></td>
                                <td><?php echo htmlspecialchars($f['heure']); ?></td>
                                <td><?php echo count($f['articles']); ?></td>
                                <td><?php echo number_format($f['total_ht'], 0, ',', ' '); ?> CDF</td>
                                <td><?php echo number_format($f['total_ttc'], 0, ',', ' '); ?> CDF</td>
                                <td>
                                    <a href="?id=<?php echo urlencode($f['id_facture']); ?>" class="btn btn-primary btn-small">Voir</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <div class="action-buttons">
        <a href="<?php echo get_base_url(); ?>modules/facturation/nouvelle-facture.php" class="btn btn-primary">Nouvelle facture</a>
        <a href="<?php echo get_base_url(); ?>index.php" class="btn btn-secondary">Retour</a>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>

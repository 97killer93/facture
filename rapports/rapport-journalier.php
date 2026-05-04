<?php
/**
 * Rapport journalier
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../auth/session.php';
require_once __DIR__ . '/../includes/fonctions-factures.php';

require_role(ROLE_MANAGER);

$page_title = 'Rapport journalier';

// Obtenir la date demandée (par défaut aujourd'hui)
$date = $_GET['date'] ?? date('Y-m-d');

// Valider la date
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    $date = date('Y-m-d');
}

$factures = obtenir_factures_par_date($date);
$stats = calculer_stats_journalieres($date);

include __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <h2>Rapport journalier</h2>
    
    <!-- Sélection de la date -->
    <div class="form-section">
        <form method="GET" class="form-inline">
            <div class="form-field">
                <label for="date">Date</label>
                <input type="date" id="date" name="date" value="<?php echo htmlspecialchars($date); ?>">
            </div>
            <button type="submit" class="btn btn-primary">Afficher</button>
        </form>
    </div>
    
    <!-- Statistiques -->
    <div class="stats-section">
        <h3>Statistiques du <?php echo date('d/m/Y', strtotime($date)); ?></h3>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?php echo $stats['nombre_factures']; ?></div>
                <div class="stat-label">Factures</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo number_format($stats['total_ht'], 0, ',', ' '); ?></div>
                <div class="stat-label">Total HT (CDF)</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo number_format($stats['total_tva'], 0, ',', ' '); ?></div>
                <div class="stat-label">TVA (CDF)</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo number_format($stats['total_ttc'], 0, ',', ' '); ?></div>
                <div class="stat-label">Total TTC (CDF)</div>
            </div>
        </div>
    </div>
    
    <!-- Liste des factures -->
    <div class="form-section">
        <h3>Factures du jour</h3>
        
        <?php if (empty($factures)): ?>
            <p class="no-data">Aucune facture pour cette date.</p>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Numéro</th>
                        <th>Heure</th>
                        <th>Caissier</th>
                        <th>Articles</th>
                        <th>Total HT</th>
                        <th>TVA</th>
                        <th>Total TTC</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($factures as $facture): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($facture['id_facture']); ?></strong></td>
                            <td><?php echo htmlspecialchars($facture['heure']); ?></td>
                            <td><?php echo htmlspecialchars($facture['caissier']); ?></td>
                            <td><?php echo count($facture['articles']); ?></td>
                            <td><?php echo number_format($facture['total_ht'], 0, ',', ' '); ?> CDF</td>
                            <td><?php echo number_format($facture['tva'], 0, ',', ' '); ?> CDF</td>
                            <td><?php echo number_format($facture['total_ttc'], 0, ',', ' '); ?> CDF</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    
    <div class="action-buttons">
        <button onclick="window.print();" class="btn btn-primary">Imprimer</button>
        <a href="<?php echo get_base_url(); ?>index.php" class="btn btn-secondary">Retour</a>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

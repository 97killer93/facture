<?php
/**
 * Page d'accueil du système
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/auth/session.php';
require_once __DIR__ . '/includes/fonctions-factures.php';

require_login();

$page_title = 'Accueil';
$user_role = $_SESSION['user_role'] ?? null;
$current_user = get_authenticated_user();

// Obtenir les statistiques du jour
$stats_jour = calculer_stats_journalieres(date('Y-m-d'));

include __DIR__ . '/includes/header.php';
?>

<div class="container">
    <div class="welcome-section">
        <h2>Bienvenue, <?php echo htmlspecialchars($current_user['nom_complet']); ?> !</h2>
        <p>Vous êtes connecté en tant que <strong><?php echo ucfirst(str_replace('_', ' ', $user_role)); ?></strong></p>
    </div>

    <?php if (has_role(ROLE_CAISSIER)): ?>
    <div class="dashboard-grid">
        <div class="dashboard-card">
            <h3>Nouvelle Facture</h3>
            <p>Créer une nouvelle facture en scannant les codes-barres</p>
            <a href="<?php echo get_base_url(); ?>modules/facturation/nouvelle-facture.php"
                class="btn btn-primary">Commencer</a>
        </div>

        <div class="dashboard-card">
            <h3> Mes Factures</h3>
            <p>Consulter l'historique de vos factures</p>
            <a href="<?php echo get_base_url(); ?>modules/facturation/afficher-facture.php"
                class="btn btn-primary">Consulter</a>
        </div>
    </div>
    <?php endif; ?>

    <?php if (has_role(ROLE_MANAGER)): ?>
    <div class="dashboard-grid">
        <div class="dashboard-card">
            <h3> Enregistrer Produit</h3>
            <p>Ajouter un nouveau produit au catalogue</p>
            <a href="<?php echo get_base_url(); ?>modules/produits/enregistrer.php"
                class="btn btn-primary">Enregistrer</a>
        </div>

        <div class="dashboard-card">
            <h3> Catalogue Produits</h3>
            <p>Consulter et gérer le catalogue</p>
            <a href="<?php echo get_base_url(); ?>modules/produits/liste.php" class="btn btn-primary">Consulter</a>
        </div>

        <div class="dashboard-card">
            <h3> Rapports</h3>
            <p>Consulter les rapports journaliers et mensuels</p>
            <a href="<?php echo get_base_url(); ?>rapports/rapport-journalier.php" class="btn btn-primary">Voir
                rapports</a>
        </div>
    </div>

    <div class="stats-section">
        <h3>Statistiques du jour (<?php echo date('d/m/Y'); ?>)</h3>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?php echo $stats_jour['nombre_factures']; ?></div>
                <div class="stat-label">Factures</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo number_format($stats_jour['total_ht'], 0, ',', ' '); ?></div>
                <div class="stat-label">Total HT (CDF)</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo number_format($stats_jour['total_ttc'], 0, ',', ' '); ?></div>
                <div class="stat-label">Total TTC (CDF)</div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if (has_role(ROLE_ADMIN)): ?>
    <div class="dashboard-grid">
        <div class="dashboard-card">
            <h3>👥 Gestion des Comptes</h3>
            <p>Créer, modifier ou supprimer des comptes utilisateurs</p>
            <a href="<?php echo get_base_url(); ?>modules/admin/gestion-comptes.php" class="btn btn-primary">Gérer</a>
        </div>

        <div class="dashboard-card">
            <h3> Enregistrer Produit</h3>
            <p>Ajouter un nouveau produit au catalogue</p>
            <a href="<?php echo get_base_url(); ?>modules/produits/enregistrer.php"
                class="btn btn-primary">Enregistrer</a>
        </div>

        <div class="dashboard-card">
            <h3> Catalogue Produits</h3>
            <p>Consulter et gérer le catalogue</p>
            <a href="<?php echo get_base_url(); ?>modules/produits/liste.php" class="btn btn-primary">Consulter</a>
        </div>

        <div class="dashboard-card">
            <h3> Rapports</h3>
            <p>Consulter les rapports journaliers et mensuels</p>
            <a href="<?php echo get_base_url(); ?>rapports/rapport-journalier.php" class="btn btn-primary">Voir
                rapports</a>
        </div>
    </div>

    <div class="stats-section">
        <h3>Statistiques du jour (<?php echo date('d/m/Y'); ?>)</h3>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?php echo $stats_jour['nombre_factures']; ?></div>
                <div class="stat-label">Factures</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo number_format($stats_jour['total_ht'], 0, ',', ' '); ?></div>
                <div class="stat-label">Total HT (CDF)</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo number_format($stats_jour['total_ttc'], 0, ',', ' '); ?></div>
                <div class="stat-label">Total TTC (CDF)</div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
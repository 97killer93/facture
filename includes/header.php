<?php
/**
 * Header réutilisable
 */

require_once __DIR__ . '/../auth/session.php';

$current_user = get_authenticated_user();
$user_role = $_SESSION['user_role'] ?? null;
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) . ' - ' : ''; ?><?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo get_base_url(); ?>assets/css/style.css">
    <?php if (isset($additional_css)): ?>
    <?php echo $additional_css; ?>
    <?php endif; ?>
</head>

<body>
    <nav class="navbar">
        <div class="navbar-container">
            <div class="navbar-brand">
                <h1> <?php echo APP_NAME; ?></h1>
            </div>

            <div class="navbar-menu">
                <?php if (is_logged_in()): ?>
                <div class="nav-links">
                    <a href="<?php echo get_base_url(); ?>index.php" class="nav-link">Accueil</a>

                    <?php if (has_role(ROLE_CAISSIER)): ?>
                    <a href="<?php echo get_base_url(); ?>modules/facturation/nouvelle-facture.php"
                        class="nav-link">Nouvelle facture</a>
                    <a href="<?php echo get_base_url(); ?>modules/facturation/afficher-facture.php" class="nav-link">Mes
                        factures</a>
                    <?php endif; ?>

                    <?php if (has_role(ROLE_MANAGER)): ?>
                    <a href="<?php echo get_base_url(); ?>modules/produits/enregistrer.php" class="nav-link">Enregistrer
                        produit</a>
                    <a href="<?php echo get_base_url(); ?>modules/produits/liste.php" class="nav-link">Catalogue</a>
                    <a href="<?php echo get_base_url(); ?>rapports/rapport-journalier.php" class="nav-link">Rapports</a>
                    <?php endif; ?>

                    <?php if (has_role(ROLE_ADMIN)): ?>
                    <a href="<?php echo get_base_url(); ?>modules/admin/gestion-comptes.php" class="nav-link">Gestion
                        comptes</a>
                    <?php endif; ?>
                </div>

                <div class="nav-user">
                    <span class="user-info">
                        <?php echo htmlspecialchars($current_user['nom_complet'] ?? 'Utilisateur'); ?>
                        <small>(<?php echo ucfirst(str_replace('_', ' ', $user_role)); ?>)</small>
                    </span>
                    <a href="<?php echo get_base_url(); ?>auth/logout.php" class="btn-logout">Déconnexion</a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <main class="main-content">
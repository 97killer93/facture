<?php
/**
 * Gestion des comptes utilisateurs
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../includes/fonctions-auth.php';

require_role(ROLE_ADMIN);

$page_title = 'Gestion des comptes';
$utilisateurs = obtenir_tous_utilisateurs();
$erreurs = [];
$succes = '';

// Traiter la soumission du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'ajouter') {
            $identifiant = trim($_POST['identifiant'] ?? '');
            $mot_de_passe = $_POST['mot_de_passe'] ?? '';
            $role = $_POST['role'] ?? '';
            $nom_complet = trim($_POST['nom_complet'] ?? '');
            
            $result = ajouter_utilisateur($identifiant, $mot_de_passe, $role, $nom_complet);
            
            if ($result['success']) {
                $succes = 'Utilisateur ajouté avec succès !';
                $utilisateurs = obtenir_tous_utilisateurs();
            } else {
                $erreurs = $result['erreurs'];
            }
        } elseif ($_POST['action'] === 'supprimer') {
            $identifiant = trim($_POST['identifiant'] ?? '');
            
            if (supprimer_utilisateur($identifiant)) {
                $succes = 'Utilisateur supprimé avec succès !';
                $utilisateurs = obtenir_tous_utilisateurs();
            } else {
                $erreurs[] = 'Erreur lors de la suppression.';
            }
        } elseif ($_POST['action'] === 'desactiver') {
            $identifiant = trim($_POST['identifiant'] ?? '');
            
            if (desactiver_utilisateur($identifiant)) {
                $succes = 'Utilisateur désactivé avec succès !';
                $utilisateurs = obtenir_tous_utilisateurs();
            } else {
                $erreurs[] = 'Erreur lors de la désactivation.';
            }
        }
    }
}

include __DIR__ . '/../../includes/header.php';
?>

<div class="container">
    <h2>Gestion des comptes utilisateurs</h2>
    
    <?php if (!empty($erreurs)): ?>
        <div class="alert alert-error">
            <ul>
                <?php foreach ($erreurs as $erreur): ?>
                    <li><?php echo htmlspecialchars($erreur); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($succes)): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($succes); ?></div>
    <?php endif; ?>
    
    <!-- Formulaire d'ajout d'utilisateur -->
    <div class="form-section">
        <h3>Ajouter un nouvel utilisateur</h3>
        <form method="POST" class="form-grid">
            <input type="hidden" name="action" value="ajouter">
            
            <div class="form-field">
                <label for="identifiant">Identifiant *</label>
                <input type="text" id="identifiant" name="identifiant" required>
            </div>
            
            <div class="form-field">
                <label for="mot_de_passe">Mot de passe *</label>
                <input type="password" id="mot_de_passe" name="mot_de_passe" required>
            </div>
            
            <div class="form-field">
                <label for="nom_complet">Nom complet *</label>
                <input type="text" id="nom_complet" name="nom_complet" required>
            </div>
            
            <div class="form-field">
                <label for="role">Rôle *</label>
                <select id="role" name="role" required>
                    <option value="">-- Sélectionner un rôle --</option>
                    <option value="<?php echo ROLE_CAISSIER; ?>">Caissier</option>
                    <option value="<?php echo ROLE_MANAGER; ?>">Manager</option>
                    <option value="<?php echo ROLE_ADMIN; ?>">Super Administrateur</option>
                </select>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Ajouter l'utilisateur</button>
                <button type="reset" class="btn btn-secondary">Réinitialiser</button>
            </div>
        </form>
    </div>
    
    <!-- Liste des utilisateurs -->
    <div class="form-section">
        <h3>Liste des utilisateurs</h3>
        
        <?php if (empty($utilisateurs)): ?>
            <p class="no-data">Aucun utilisateur.</p>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Identifiant</th>
                        <th>Nom complet</th>
                        <th>Rôle</th>
                        <th>Date création</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($utilisateurs as $user): ?>
                        <tr>
                            <td><code><?php echo htmlspecialchars($user['identifiant']); ?></code></td>
                            <td><?php echo htmlspecialchars($user['nom_complet']); ?></td>
                            <td>
                                <span class="role-badge role-<?php echo str_replace('_', '-', $user['role']); ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $user['role'])); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($user['date_creation']); ?></td>
                            <td>
                                <span class="status-badge <?php echo $user['actif'] ? 'status-active' : 'status-inactive'; ?>">
                                    <?php echo $user['actif'] ? 'Actif' : 'Inactif'; ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($user['identifiant'] !== $_SESSION['user_id']): ?>
                                    <?php if ($user['actif']): ?>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="action" value="desactiver">
                                            <input type="hidden" name="identifiant" value="<?php echo htmlspecialchars($user['identifiant']); ?>">
                                            <button type="submit" class="btn btn-warning btn-small">Désactiver</button>
                                        </form>
                                    <?php endif; ?>
                                    
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Êtes-vous sûr ?');">
                                        <input type="hidden" name="action" value="supprimer">
                                        <input type="hidden" name="identifiant" value="<?php echo htmlspecialchars($user['identifiant']); ?>">
                                        <button type="submit" class="btn btn-danger btn-small">Supprimer</button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-muted">Compte actuel</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    
    <div class="action-buttons">
        <a href="<?php echo get_base_url(); ?>index.php" class="btn btn-secondary">Retour</a>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>

<?php
/**
 * Module d'enregistrement de produits
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../auth/session.php';
require_once __DIR__ . '/../../includes/fonctions-produits.php';

require_role(ROLE_MANAGER);

$page_title = 'Enregistrer un produit';
$produit_trouve = null;
$erreurs = [];
$succes = '';
$code_barre = '';
$formulaire_visible = false;
$nom = '';
$prix_unitaire_ht = '';
$date_expiration = '';
$quantite_stock = '';

// Traiter la soumission du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'scanner') {
            $code_barre = trim($_POST['code_barre'] ?? '');
            
            if (empty($code_barre)) {
                $erreurs[] = 'Veuillez entrer un code-barres.';
            } else {
                $produit_trouve = chercher_produit_par_code($code_barre);
                
                if ($produit_trouve) {
                    $succes = 'Ce produit existe déjà.';
                } else {
                    $formulaire_visible = true;
                }
            }
        } elseif ($_POST['action'] === 'enregistrer') {
            $code_barre = trim($_POST['code_barre'] ?? '');
            $nom = trim($_POST['nom'] ?? '');
            $prix_unitaire_ht = trim($_POST['prix_unitaire_ht'] ?? '');
            $date_expiration = trim($_POST['date_expiration'] ?? '');
            $quantite_stock = trim($_POST['quantite_stock'] ?? '');
            
            $result = ajouter_produit($code_barre, $nom, $prix_unitaire_ht, $date_expiration, $quantite_stock);
            
            if ($result['success']) {
                $succes = 'Produit enregistré avec succès !';
                $code_barre = '';
                $nom = '';
                $prix_unitaire_ht = '';
                $date_expiration = '';
                $quantite_stock = '';
                $formulaire_visible = false;
            } else {
                $erreurs = $result['erreurs'];
                $formulaire_visible = true;
            }
        }
    }
}

include __DIR__ . '/../../includes/header.php';
?>

<div class="container">
    <h2>Enregistrer un nouveau produit</h2>
    
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
        <div class="alert alert-success">
            <?php echo htmlspecialchars($succes); ?>
        </div>
    <?php endif; ?>
    
    <!-- Section scanner -->
    <div class="form-section">
        <h3>Étape 1 : Scanner le code-barres</h3>
        <button type="button" id="activer-camera" class="btn btn-secondary">Activer la caméra</button>
        <button type="button" id="arreter-camera" class="btn btn-danger" style="display:none;">Arrêter la caméra</button>
        <p id="camera-status" class="camera-status">Caméra inactive.</p>
        <div id="camera-zone" style="display:none;">
            <video id="camera-preview" autoplay playsinline style="width:100%;max-width:480px;border:1px solid #ddd;border-radius:6px;"></video>
        </div>
        <form method="POST" class="form-group">
            <input type="hidden" name="action" value="scanner">
            
            <div class="form-field">
                <label for="code_barre">Code-barres</label>
                <input type="text" id="code_barre" name="code_barre" value="<?php echo htmlspecialchars($code_barre); ?>" placeholder="Scannez ou entrez le code-barres" autofocus>
            </div>
            
            <button type="submit" class="btn btn-primary">Vérifier</button>
        </form>
    </div>
    
    <!-- Afficher le produit existant -->
    <?php if ($produit_trouve): ?>
        <div class="form-section">
            <h3>Produit existant</h3>
            <div class="product-info">
                <p><strong>Nom :</strong> <?php echo htmlspecialchars($produit_trouve['nom']); ?></p>
                <p><strong>Code-barres :</strong> <?php echo htmlspecialchars($produit_trouve['code_barre']); ?></p>
                <p><strong>Prix unitaire HT :</strong> <?php echo number_format($produit_trouve['prix_unitaire_ht'], 0, ',', ' '); ?> CDF</p>
                <p><strong>Date d'expiration :</strong> <?php echo htmlspecialchars($produit_trouve['date_expiration']); ?></p>
                <p><strong>Quantité en stock :</strong> <?php echo htmlspecialchars($produit_trouve['quantite_stock']); ?></p>
                <p><strong>Date d'enregistrement :</strong> <?php echo htmlspecialchars($produit_trouve['date_enregistrement']); ?></p>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- Formulaire d'enregistrement -->
    <?php if ($formulaire_visible): ?>
        <div class="form-section">
            <h3>Étape 2 : Informations du produit</h3>
            <form method="POST" class="form-grid">
                <input type="hidden" name="action" value="enregistrer">
                <input type="hidden" name="code_barre" value="<?php echo htmlspecialchars($code_barre); ?>">
                
                <div class="form-field">
                    <label for="nom">Nom du produit *</label>
                    <input type="text" id="nom" name="nom" value="<?php echo htmlspecialchars($nom); ?>" required>
                </div>
                
                <div class="form-field">
                    <label for="prix_unitaire_ht">Prix unitaire HT (CDF) *</label>
                    <input type="number" id="prix_unitaire_ht" name="prix_unitaire_ht" min="0" value="<?php echo htmlspecialchars($prix_unitaire_ht); ?>" required>
                </div>
                
                <div class="form-field">
                    <label for="date_expiration">Date d'expiration (MM-JJ-AAAA) *</label>
                    <input type="text" id="date_expiration" name="date_expiration" placeholder="MM-JJ-AAAA" pattern="^\d{2}-\d{2}-\d{4}$" value="<?php echo htmlspecialchars($date_expiration); ?>" required>
                </div>
                
                <div class="form-field">
                    <label for="quantite_stock">Quantité en stock *</label>
                    <input type="number" id="quantite_stock" name="quantite_stock" min="0" value="<?php echo htmlspecialchars($quantite_stock); ?>" required>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Enregistrer le produit</button>
                    <button type="reset" class="btn btn-secondary">Réinitialiser</button>
                </div>
            </form>
        </div>
    <?php endif; ?>
</div>

<script>
(() => {
    const startBtn = document.getElementById('activer-camera');
    const stopBtn = document.getElementById('arreter-camera');
    const statusText = document.getElementById('camera-status');
    const cameraZone = document.getElementById('camera-zone');
    const video = document.getElementById('camera-preview');
    const codeInput = document.getElementById('code_barre');
    const scanForm = codeInput ? codeInput.form : null;

    if (!startBtn || !stopBtn || !statusText || !cameraZone || !video || !codeInput || !scanForm) {
        return;
    }

    let stream = null;
    let detector = null;
    let scanInterval = null;

    const stopCamera = () => {
        if (scanInterval) {
            clearInterval(scanInterval);
            scanInterval = null;
        }
        if (stream) {
            stream.getTracks().forEach((track) => track.stop());
            stream = null;
        }
        video.srcObject = null;
        cameraZone.style.display = 'none';
        startBtn.style.display = 'inline-block';
        stopBtn.style.display = 'none';
        statusText.textContent = 'Caméra inactive.';
    };

    const handleDetectedCode = (rawCode) => {
        const code = String(rawCode || '').trim();
        if (!code) {
            return;
        }
        codeInput.value = code;
        statusText.textContent = `Code détecté: ${code}. Vérification en cours...`;
        stopCamera();
        scanForm.submit();
    };

    const startScanLoop = () => {
        scanInterval = setInterval(async () => {
            if (!detector || video.readyState < 2) {
                return;
            }
            try {
                const barcodes = await detector.detect(video);
                if (barcodes && barcodes.length > 0 && barcodes[0].rawValue) {
                    handleDetectedCode(barcodes[0].rawValue);
                }
            } catch (error) {
                statusText.textContent = 'Erreur de lecture du code-barres. Réessayez.';
            }
        }, 600);
    };

    startBtn.addEventListener('click', async () => {
        if (!('BarcodeDetector' in window)) {
            statusText.textContent = 'Votre navigateur ne supporte pas BarcodeDetector. Utilisez la saisie manuelle.';
            return;
        }

        try {
            detector = new window.BarcodeDetector({
                formats: ['ean_13', 'ean_8', 'code_128', 'upc_a', 'upc_e']
            });
            stream = await navigator.mediaDevices.getUserMedia({
                video: {facingMode: {ideal: 'environment'}}
            });
            video.srcObject = stream;
            cameraZone.style.display = 'block';
            startBtn.style.display = 'none';
            stopBtn.style.display = 'inline-block';
            statusText.textContent = 'Caméra active. Placez le code-barres dans le cadre.';
            startScanLoop();
        } catch (error) {
            statusText.textContent = 'Impossible d\'activer la caméra. Vérifiez les permissions.';
            stopCamera();
        }
    });

    stopBtn.addEventListener('click', stopCamera);
    window.addEventListener('beforeunload', stopCamera);
})();
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>

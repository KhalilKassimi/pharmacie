<?php
session_start();
require_once '../config.php';
require_once '../includes/auth.php';

requireAuth();

// Récupération de la liste des clients
try {
    $stmt = $pdo->query("SELECT * FROM clients ORDER BY nom, prenom");
    $clients = $stmt->fetchAll();
} catch(PDOException $e) {
    $_SESSION['message'] = 'Erreur lors de la récupération des clients : ' . $e->getMessage();
    $_SESSION['message_type'] = 'danger';
    $clients = [];
}

// Récupération de la liste des médicaments
try {
    $stmt = $pdo->query("SELECT * FROM medicaments ORDER BY nom");
    $medicaments = $stmt->fetchAll();
} catch(PDOException $e) {
    $_SESSION['message'] = 'Erreur lors de la récupération des médicaments : ' . $e->getMessage();
    $_SESSION['message_type'] = 'danger';
    $medicaments = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $client_id = !empty($_POST['client_id']) ? intval($_POST['client_id']) : null;
    $medicaments_vendus = isset($_POST['medicaments']) ? $_POST['medicaments'] : [];
    $quantites = isset($_POST['quantites']) ? $_POST['quantites'] : [];
    $total_montant = 0;

    if (empty($medicaments_vendus)) {
        $_SESSION['message'] = 'Veuillez sélectionner au moins un médicament';
        $_SESSION['message_type'] = 'danger';
    } else {
        try {
            $pdo->beginTransaction();

            // Création de la vente
            $stmt = $pdo->prepare("INSERT INTO ventes (client_id, date_vente, total_montant, status) VALUES (?, NOW(), 0, 'pending')");
            $stmt->execute([$client_id]);
            $vente_id = $pdo->lastInsertId();

            // Ajout des médicaments à la vente
            $stmt_prix = $pdo->prepare("SELECT prix_unitaire, stock_quantite FROM medicaments WHERE id = ?");
            $stmt_ligne = $pdo->prepare("INSERT INTO vente_details (vente_id, medicament_id, quantite, prix_unitaire) VALUES (?, ?, ?, ?)");
            $stmt_update_stock = $pdo->prepare("UPDATE medicaments SET stock_quantite = stock_quantite - ? WHERE id = ?");

            foreach ($medicaments_vendus as $index => $medicament_id) {
                $quantite = intval($quantites[$index]);
                if ($quantite > 0) {
                    // Vérification du stock
                    $stmt_prix->execute([$medicament_id]);
                    $med_info = $stmt_prix->fetch();
                    
                    if ($med_info['stock_quantite'] < $quantite) {
                        throw new Exception("Stock insuffisant pour le médicament ID: $medicament_id");
                    }

                    $prix = $med_info['prix_unitaire'];
                    $total_montant += $prix * $quantite;

                    // Ajout de la ligne de vente
                    $stmt_ligne->execute([$vente_id, $medicament_id, $quantite, $prix]);
                    
                    // Mise à jour du stock
                    $stmt_update_stock->execute([$quantite, $medicament_id]);
                }
            }

            // Mise à jour du montant total de la vente
            $stmt = $pdo->prepare("UPDATE ventes SET total_montant = ? WHERE id = ?");
            $stmt->execute([$total_montant, $vente_id]);

            $pdo->commit();
            $_SESSION['message'] = 'Vente créée avec succès';
            $_SESSION['message_type'] = 'success';
            header('Location: index.php');
            exit();

        } catch(Exception $e) {
            $pdo->rollBack();
            $_SESSION['message'] = 'Erreur lors de la création de la vente : ' . $e->getMessage();
            $_SESSION['message_type'] = 'danger';
        }
    }
}

require_once '../includes/header.php';
?>

<div class="container">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h2 mb-0">
                <i class="bi bi-receipt"></i> Nouvelle Vente
            </h1>
        </div>
    </div>

    <?php if(isset($_SESSION['message'])): ?>
    <div class="alert alert-<?php echo $_SESSION['message_type']; ?> alert-dismissible fade show" role="alert">
        <?php 
        echo $_SESSION['message'];
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
        ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST" class="needs-validation" novalidate>
                <div class="row g-3">
                    <div class="col-md-12">
                        <label for="client_id" class="form-label">Client</label>
                        <select id="client_id" name="client_id" class="form-select">
                            <option value="">Client occasionnel</option>
                            <?php foreach($clients as $client): ?>
                                <option value="<?php echo $client['id']; ?>">
                                    <?php echo htmlspecialchars($client['prenom'] . ' ' . $client['nom']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div id="medicaments-container" class="mt-4">
                    <h3 class="h4 mb-3">Médicaments</h3>
                    <div class="medicament-ligne">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label">Médicament*</label>
                                <select name="medicaments[]" class="form-select" required>
                                    <option value="">Sélectionnez un médicament</option>
                                    <?php foreach($medicaments as $medicament): ?>
                                        <option value="<?php echo $medicament['id']; ?>" 
                                                data-prix="<?php echo $medicament['prix_unitaire']; ?>"
                                                data-stock="<?php echo $medicament['stock_quantite']; ?>">
                                            <?php echo htmlspecialchars($medicament['nom']); ?>
                                            (Stock: <?php echo $medicament['stock_quantite']; ?>, 
                                             Prix: <?php echo number_format($medicament['prix_unitaire'], 2, ',', ' '); ?> €)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback">Veuillez sélectionner un médicament</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Quantité*</label>
                                <input type="number" name="quantites[]" class="form-control" min="1" value="1" required>
                                <div class="invalid-feedback">La quantité doit être supérieure à 0</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-3">
                    <button type="button" class="btn btn-outline-primary" onclick="ajouterLigne()">
                        <i class="bi bi-plus-lg"></i> Ajouter un médicament
                    </button>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="index.php" class="btn btn-light">
                        <i class="bi bi-x-lg"></i> Annuler
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg"></i> Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.medicament-ligne {
    margin-bottom: 1rem;
    padding: 1rem;
    border: 1px solid var(--bs-border-color);
    border-radius: var(--bs-border-radius);
    position: relative;
}

.btn-remove {
    position: absolute;
    top: 0.5rem;
    right: 0.5rem;
    width: 32px;
    height: 32px;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>

<script>
// Form validation
(function () {
    'use strict'
    var forms = document.querySelectorAll('.needs-validation')
    Array.prototype.slice.call(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault()
                event.stopPropagation()
            }
            form.classList.add('was-validated')
        }, false)
    })
})()

function ajouterLigne() {
    const container = document.getElementById('medicaments-container');
    const ligne = document.querySelector('.medicament-ligne').cloneNode(true);
    
    // Réinitialiser les valeurs
    ligne.querySelector('select').value = '';
    ligne.querySelector('input[type="number"]').value = 1;
    
    // Ajouter le bouton de suppression
    const btnRemove = document.createElement('button');
    btnRemove.type = 'button';
    btnRemove.className = 'btn btn-outline-danger btn-sm btn-remove';
    btnRemove.innerHTML = '<i class="bi bi-trash"></i>';
    btnRemove.onclick = function() {
        ligne.remove();
    };
    ligne.appendChild(btnRemove);
    
    container.appendChild(ligne);
}

// Stock validation
document.addEventListener('change', function(e) {
    if (e.target.matches('select[name="medicaments[]"]')) {
        const option = e.target.selectedOptions[0];
        const stock = parseInt(option.dataset.stock);
        const quantityInput = e.target.closest('.row').querySelector('input[name="quantites[]"]');
        quantityInput.max = stock;
        
        if (parseInt(quantityInput.value) > stock) {
            quantityInput.value = stock;
        }
    }
});

document.addEventListener('input', function(e) {
    if (e.target.matches('input[name="quantites[]"]')) {
        const select = e.target.closest('.row').querySelector('select[name="medicaments[]"]');
        const option = select.selectedOptions[0];
        if (option.value) {
            const stock = parseInt(option.dataset.stock);
            if (parseInt(e.target.value) > stock) {
                e.target.value = stock;
            }
        }
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>
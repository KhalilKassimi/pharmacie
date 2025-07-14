<?php
require_once '../includes/db.php';
require_once '../includes/header.php';

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$id = $_GET['id'];

// Récupération des informations de la vente
$stmt = $pdo->prepare("
    SELECT v.*, p.nom as pharmacien_nom 
    FROM vente v 
    LEFT JOIN pharmacien p ON v.code_pharmacien = p.code_pharmacien 
    WHERE v.num_vente = ?");
$stmt->execute([$id]);
$vente = $stmt->fetch();

if (!$vente) {
    $_SESSION['error'] = 'Vente non trouvée';
    header('Location: index.php');
    exit;
}

// Récupération des médicaments de la vente
$stmt = $pdo->prepare("
    SELECT c.*, m.description, m.prixunitaire 
    FROM concerne c 
    JOIN medicament m ON c.code_medi = m.code_medi 
    WHERE c.num_vente = ?");
$stmt->execute([$id]);
$medicaments = $stmt->fetchAll();
?>

<div class="card">
    <h1>Détails de la Vente #<?= htmlspecialchars($vente['num_vente']) ?></h1>

    <div class="row mb-4">
        <div class="col-md-6">
            <h3>Informations Générales</h3>
            <table class="table table-bordered">
                <tr>
                    <th>Date</th>
                    <td><?= htmlspecialchars($vente['date_vente']) ?></td>
                </tr>
                <tr>
                    <th>Pharmacien</th>
                    <td><?= htmlspecialchars($vente['pharmacien_nom']) ?></td>
                </tr>
                <tr>
                    <th>Montant Total</th>
                    <td><?= number_format($vente['montant_vente'], 2, ',', ' ') ?> MAD</td>
                </tr>
            </table>
        </div>
    </div>

    <h3>Médicaments Vendus</h3>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Médicament</th>
                <th>Quantité</th>
                <th>Prix Unitaire</th>
                <th>Sous-total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($medicaments as $medicament): ?>
                <tr>
                    <td><?= htmlspecialchars($medicament['description']) ?></td>
                    <td><?= htmlspecialchars($medicament['quantite']) ?></td>
                    <td><?= number_format($medicament['prixunitaire'], 2, ',', ' ') ?> MAD</td>
                    <td><?= number_format($medicament['prixunitaire'] * $medicament['quantite'], 2, ',', ' ') ?> MAD</td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="3" class="text-right">Total</th>
                <th><?= number_format($vente['montant_vente'], 2, ',', ' ') ?> MAD</th>
            </tr>
        </tfoot>
    </table>

    <div class="mt-3">
        <a href="index.php" class="btn btn-secondary">Retour à la liste</a>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
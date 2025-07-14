<?php
session_start();
require_once '../config.php';
require_once '../includes/auth.php';

requireAuth();

// Récupération de toutes les ventes avec les informations du client
try {
    $stmt = $pdo->query("SELECT v.*, c.nom as client_nom, c.prenom as client_prenom 
                         FROM ventes v 
                         LEFT JOIN clients c ON v.client_id = c.id 
                         ORDER BY v.date_vente DESC");
    $ventes = $stmt->fetchAll();
} catch(PDOException $e) {
    $_SESSION['message'] = 'Erreur lors de la récupération des ventes : ' . $e->getMessage();
    $_SESSION['message_type'] = 'danger';
    $ventes = [];
}

require_once '../includes/header.php';
?>

<div class="container">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h2 mb-0">
                <i class="bi bi-receipt"></i> Gestion des Ventes
            </h1>
            <a href="create.php" class="btn btn-primary mt-3">
                <i class="bi bi-plus-lg"></i> Nouvelle Vente
            </a>
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
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Date</th>
                            <th>Client</th>
                            <th>Montant</th>
                            <th>Statut</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($ventes as $vente): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($vente['id']); ?></td>
                            <td>
                                <?php 
                                $date = new DateTime($vente['date_vente']);
                                echo $date->format('d/m/Y H:i');
                                ?>
                            </td>
                            <td>
                                <?php 
                                if ($vente['client_id']) {
                                    echo htmlspecialchars($vente['client_prenom'] . ' ' . $vente['client_nom']);
                                } else {
                                    echo '<span class="text-muted">Client occasionnel</span>';
                                }
                                ?>
                            </td>
                            <td><?php echo number_format($vente['total_montant'], 2, ',', ' '); ?> €</td>
                            <td>
                                <?php if($vente['status'] === 'completed'): ?>
                                    <span class="badge bg-success">Validée</span>
                                <?php elseif($vente['status'] === 'cancelled'): ?>
                                    <span class="badge bg-danger">Annulée</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark">En attente</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <a href="view.php?id=<?php echo $vente['id']; ?>" 
                                   class="btn btn-sm btn-outline-info">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <?php if($vente['status'] === 'pending'): ?>
                                    <a href="edit.php?id=<?php echo $vente['id']; ?>" 
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="index.php?validate=<?php echo $vente['id']; ?>" 
                                       class="btn btn-sm btn-outline-success"
                                       onclick="return confirm('Êtes-vous sûr de vouloir valider cette vente ?')">
                                        <i class="bi bi-check-lg"></i>
                                    </a>
                                    <a href="index.php?delete=<?php echo $vente['id']; ?>" 
                                       class="btn btn-sm btn-outline-danger"
                                       onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette vente ?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($ventes)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                Aucune vente trouvée
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
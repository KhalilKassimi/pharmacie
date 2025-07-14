<?php
require_once '../includes/db.php';

if (!isset($_GET['id'])) {
    $_SESSION['error'] = 'ID de la vente non spécifié';
    header('Location: index.php');
    exit;
}

$id = $_GET['id'];

try {
    $pdo->beginTransaction();

    // Récupération des médicaments vendus pour mettre à jour le stock
    $stmt = $pdo->prepare("SELECT code_medi, quantite FROM concerne WHERE num_vente = ?");
    $stmt->execute([$id]);
    $medicaments = $stmt->fetchAll();

    // Mise à jour du stock pour chaque médicament
    $stmt_stock = $pdo->prepare("UPDATE medicament SET stock_dispo = stock_dispo + ? WHERE code_medi = ?");
    foreach ($medicaments as $medicament) {
        $stmt_stock->execute([$medicament['quantite'], $medicament['code_medi']]);
    }

    // Suppression des lignes de la vente dans la table concerne
    $stmt = $pdo->prepare("DELETE FROM concerne WHERE num_vente = ?");
    $stmt->execute([$id]);

    // Suppression de la vente
    $stmt = $pdo->prepare("DELETE FROM vente WHERE num_vente = ?");
    $stmt->execute([$id]);

    $pdo->commit();
    $_SESSION['message'] = 'Vente supprimée avec succès';
} catch (PDOException $e) {
    $pdo->rollBack();
    $_SESSION['error'] = 'Une erreur est survenue lors de la suppression de la vente';
}

header('Location: index.php');
exit;
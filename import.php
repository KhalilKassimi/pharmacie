<?php
session_start();
require_once '../config.php';

// Vérifier si le fichier a été téléchargé
if (!isset($_FILES['excelFile']) || $_FILES['excelFile']['error'] !== UPLOAD_ERR_OK) {
    $_SESSION['message'] = 'Erreur lors du téléchargement du fichier';
    $_SESSION['message_type'] = 'danger';
    header('Location: index.php');
    exit();
}

// Vérifier l'extension du fichier
$allowedExtensions = ['csv'];
$fileExtension = strtolower(pathinfo($_FILES['excelFile']['name'], PATHINFO_EXTENSION));
if (!in_array($fileExtension, $allowedExtensions)) {
    $_SESSION['message'] = 'Format de fichier non supporté. Veuillez utiliser un fichier CSV (.csv)';
    $_SESSION['message_type'] = 'danger';
    header('Location: index.php');
    exit();
}

try {
    // Ouvrir le fichier CSV
    $handle = fopen($_FILES['excelFile']['tmp_name'], 'r');
    if (!$handle) {
        throw new Exception('Impossible d\'ouvrir le fichier');
    }
    
    // Lire les en-têtes
    $headers = fgetcsv($handle, 0, ';');
    if (!$headers) {
        throw new Exception('Impossible de lire les en-têtes du fichier');
    }
    
    $requiredHeaders = ['nom', 'description', 'stock_minimum', 'prix_unitaire', 'stock_quantite'];
    foreach ($requiredHeaders as $header) {
        if (!in_array($header, $headers)) {
            throw new Exception('En-tête manquant : ' . $header);
        }
    }
    
    $totalImported = 0;
    $errors = [];
    
    // Lire chaque ligne du CSV
    while (($row = fgetcsv($handle, 0, ';')) !== false) {
        if (count($row) < 5) continue; // Ignorer les lignes incomplètes
        
        $nom = trim($row[0]);
        $description = trim($row[1]);
        $stock_minimum = trim($row[2]);
        $prix_unitaire = trim($row[3]);
        $stock_quantite = trim($row[4]);
        
        // Validation des données
        if (empty($nom) || empty($description) || empty($stock_minimum) || empty($prix_unitaire) || empty($stock_quantite)) {
            $errors[] = "Données manquantes pour le médicament: " . $nom;
            continue;
        }
        
        try {
            $stmt = $pdo->prepare("
                INSERT INTO medicaments (nom, description, stock_minimum, prix_unitaire, stock_quantite)
                VALUES (?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $nom,
                $description,
                (float)$stock_minimum,
                (float)$prix_unitaire,
                (float)$stock_quantite
            ]);
            
            $totalImported++;
        } catch (PDOException $e) {
            $errors[] = "Erreur lors de l'insertion du médicament " . $nom . ": " . $e->getMessage();
        }
    }
    
    fclose($handle);
    
    // Préparer le message de succès
    $message = "Importation terminée. " . $totalImported . " médicaments ont été importés.";
    
    // Ajouter les erreurs si elles existent
    if (!empty($errors)) {
        $message .= "\nErreurs rencontrées:\n" . implode("\n", $errors);
    }
    
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = 'success';
    
} catch (Exception $e) {
    $_SESSION['message'] = 'Erreur lors de l\'importation : ' . $e->getMessage();
    $_SESSION['message_type'] = 'danger';
}

header('Location: index.php');
exit();

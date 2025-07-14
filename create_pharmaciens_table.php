<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = 'localhost';
$dbname = 'pharmacies_db';
$username = 'root';
$password = '';

try {
    // First connect without database
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname`");
    echo "✅ Base de données '$dbname' créée/vérifiée\n\n";
    
    // Select the database
    $pdo->exec("USE `$dbname`");
    echo "✅ Base de données '$dbname' sélectionnée\n\n";
    
    // Show all tables before creation
    echo "Tables existantes avant création:\n";
    $stmt = $pdo->query("SHOW TABLES");
    while($row = $stmt->fetch(PDO::FETCH_NUM)) {
        echo "- " . $row[0] . "\n";
    }
    echo "\n";
    
    // Drop the table if it exists
    $pdo->exec("DROP TABLE IF EXISTS pharmaciens");
    echo "Table pharmaciens supprimée si elle existait\n\n";
    
    // Create pharmaciens table
    $sql = "CREATE TABLE pharmaciens (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nom VARCHAR(100) NOT NULL,
        prenom VARCHAR(100) NOT NULL,
        telephone VARCHAR(20),
        email VARCHAR(100),
        adresse TEXT,
        date_embauche DATE,
        role ENUM('admin', 'pharmacist') DEFAULT 'pharmacist',
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        date_creation DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql);
    echo "✅ Table pharmaciens créée\n\n";
    
    // Show all tables after creation
    echo "Tables existantes après création:\n";
    $stmt = $pdo->query("SHOW TABLES");
    while($row = $stmt->fetch(PDO::FETCH_NUM)) {
        echo "- " . $row[0] . "\n";
    }
    echo "\n";
    
    // Verify pharmaciens table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'pharmaciens'");
    if ($stmt->rowCount() > 0) {
        echo "✅ Vérification: La table pharmaciens existe\n\n";
        
        // Show table structure
        $stmt = $pdo->query("DESCRIBE pharmaciens");
        echo "Structure de la table pharmaciens:\n";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "- {$row['Field']}: {$row['Type']}\n";
        }
        
        // Create test pharmacist
        $stmt = $pdo->prepare("INSERT INTO pharmaciens (nom, prenom, username, password, role) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute(['Test', 'Pharmacien', 'pharmacien1', password_hash('pharmacien123', PASSWORD_DEFAULT), 'pharmacist']);
        echo "\n✅ Pharmacien test créé avec succès\n";
        echo "👤 Nom d'utilisateur: pharmacien1\n";
        echo "🔑 Mot de passe: pharmacien123\n";
    } else {
        echo "❌ ERREUR: La table pharmaciens n'existe pas après la création!\n";
    }
    
} catch(PDOException $e) {
    echo "\n❌ ERREUR MySQL:\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "Code: " . $e->getCode() . "\n";
    echo "Fichier: " . $e->getFile() . "\n";
    echo "Ligne: " . $e->getLine() . "\n";
    
    // Get MySQL error info if available
    if ($pdo && $pdo->errorInfo()) {
        $errorInfo = $pdo->errorInfo();
        echo "\nMySQL Error Info:\n";
        echo "SQLSTATE: " . $errorInfo[0] . "\n";
        echo "Error Code: " . $errorInfo[1] . "\n";
        echo "Message: " . $errorInfo[2] . "\n";
    }
}

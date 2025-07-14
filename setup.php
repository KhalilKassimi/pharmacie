<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = "localhost";
$dbname = "pharmacies_db";
$username = "root";
$password = "";

try {
    // Create database if it doesn't exist
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $pdo->exec("CREATE DATABASE IF NOT EXISTS $dbname");
    $pdo->exec("USE $dbname");
    
    echo "✅ Base de données créée/sélectionnée avec succès\n\n";
    
    // Create users table with additional columns
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        role VARCHAR(20) NOT NULL,
        email VARCHAR(100),
        last_login DATETIME,
        last_logout DATETIME,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    $pdo->exec($sql);
    echo "✅ Table users créée avec succès\n";

    // Create login_attempts table for security
    $sql = "CREATE TABLE IF NOT EXISTS login_attempts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL,
        ip_address VARCHAR(45) NOT NULL,
        success BOOLEAN DEFAULT 0,
        attempt_time DATETIME NOT NULL,
        INDEX idx_username (username),
        INDEX idx_attempt_time (attempt_time)
    )";
    
    $pdo->exec($sql);
    echo "✅ Table login_attempts créée avec succès\n";

    // Create medicaments (medicines) table
    $sql = "CREATE TABLE IF NOT EXISTS medicaments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nom VARCHAR(100) NOT NULL,
        description TEXT,
        prix_unitaire DECIMAL(10,2) NOT NULL,
        stock_quantite INT NOT NULL DEFAULT 0,
        stock_minimum INT NOT NULL DEFAULT 10,
        categorie VARCHAR(50),
        fabricant VARCHAR(100),
        date_expiration DATE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    $pdo->exec($sql);
    echo "✅ Table medicaments créée avec succès\n";

    // Create clients table
    $sql = "CREATE TABLE IF NOT EXISTS clients (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nom VARCHAR(100) NOT NULL,
        prenom VARCHAR(100) NOT NULL,
        telephone VARCHAR(20),
        email VARCHAR(100),
        adresse TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    $pdo->exec($sql);
    echo "✅ Table clients créée avec succès\n";

    // Create fournisseurs (suppliers) table
    $sql = "CREATE TABLE IF NOT EXISTS fournisseurs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nom VARCHAR(100) NOT NULL,
        contact_nom VARCHAR(100),
        telephone VARCHAR(20),
        email VARCHAR(100),
        adresse TEXT,
        date_creation DATETIME DEFAULT CURRENT_TIMESTAMP
    )";
    
    $pdo->exec($sql);
    echo "✅ Table fournisseurs créée avec succès\n";

    // Create ventes (sales) table
    $sql = "CREATE TABLE IF NOT EXISTS ventes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        client_id INT,
        user_id INT NOT NULL,
        date_vente DATETIME DEFAULT CURRENT_TIMESTAMP,
        total_montant DECIMAL(10,2) NOT NULL,
        status VARCHAR(20) DEFAULT 'completed',
        FOREIGN KEY (client_id) REFERENCES clients(id),
        FOREIGN KEY (user_id) REFERENCES users(id)
    )";
    
    $pdo->exec($sql);
    echo "✅ Table ventes créée avec succès\n";

    // Create vente_details (sale details) table
    $sql = "CREATE TABLE IF NOT EXISTS vente_details (
        id INT AUTO_INCREMENT PRIMARY KEY,
        vente_id INT NOT NULL,
        medicament_id INT NOT NULL,
        quantite INT NOT NULL,
        prix_unitaire DECIMAL(10,2) NOT NULL,
        FOREIGN KEY (vente_id) REFERENCES ventes(id),
        FOREIGN KEY (medicament_id) REFERENCES medicaments(id)
    )";
    
    $pdo->exec($sql);
    echo "✅ Table vente_details créée avec succès\n";

    // Create commandes (orders) table
    $sql = "CREATE TABLE IF NOT EXISTS commandes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        fournisseur_id INT,
        date_commande DATETIME DEFAULT CURRENT_TIMESTAMP,
        total_montant DECIMAL(10,2) NOT NULL DEFAULT 0,
        status ENUM('pending', 'completed', 'cancelled') DEFAULT 'pending',
        FOREIGN KEY (fournisseur_id) REFERENCES fournisseurs(id) ON DELETE SET NULL
    )";
    
    $pdo->exec($sql);
    echo "✅ Table commandes créée avec succès\n";

    // Create commande_details (order details) table
    $sql = "CREATE TABLE IF NOT EXISTS commande_details (
        id INT AUTO_INCREMENT PRIMARY KEY,
        commande_id INT NOT NULL,
        medicament_id INT NOT NULL,
        quantite INT NOT NULL,
        prix_unitaire DECIMAL(10,2) NOT NULL,
        FOREIGN KEY (commande_id) REFERENCES commandes(id) ON DELETE CASCADE,
        FOREIGN KEY (medicament_id) REFERENCES medicaments(id) ON DELETE RESTRICT
    )";
    
    $pdo->exec($sql);
    echo "✅ Table commande_details créée avec succès\n";

    // Create pharmaciens (pharmacists) table
    $sql = "CREATE TABLE IF NOT EXISTS pharmaciens (
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
    )";
    
    $pdo->exec($sql);
    echo "✅ Table pharmaciens créée avec succès\n";

    // Check if admin user exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute(['admin']);
    $adminExists = $stmt->fetch();

    if (!$adminExists) {
        // Create admin user with password 'admin123'
        $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, password, role, email) VALUES (?, ?, ?, ?)");
        $stmt->execute(['admin', $hashedPassword, 'admin', 'admin@pharma.local']);
        echo "✅ Administrateur créé avec succès\n";
        echo "👤 Nom d'utilisateur: admin\n";
        echo "🔑 Mot de passe: admin123\n\n";
    } else {
        echo "ℹ️ L'utilisateur administrateur existe déjà.\n\n";
    }

    // Verify tables exist
    $tables = ['users', 'pharmaciens', 'clients', 'fournisseurs', 'medicaments', 'ventes', 'vente_details', 'commandes', 'commande_details'];
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "✅ Table $table existe\n";
        } else {
            echo "❌ Table $table n'existe pas!\n";
        }
    }

    echo "\n✅ Configuration de la base de données terminée avec succès!\n";
} catch (PDOException $e) {
    echo "❌ Erreur lors de la configuration : " . $e->getMessage() . "\n";
    echo "📍 Ligne : " . $e->getLine() . "\n";
    echo "🔍 Trace : \n" . $e->getTraceAsString() . "\n";
}
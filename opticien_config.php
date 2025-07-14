<?php
// Configuration de la base de données
$host = "localhost";
$dbname = "opticien_db";
$username = "root";
$password = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->exec("SET NAMES utf8");
} catch(PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Configuration générale
define('SITE_TITLE', 'Cabinet Opticien');
define('CURRENCY', '€');
define('TVA_RATE', 20); // Taux de TVA en pourcentage

// Configuration des chemins
define('BASE_PATH', dirname(__DIR__));
define('UPLOAD_PATH', BASE_PATH . '/uploads');

// Configuration des sessions
session_start();

// Fonctions utilitaires
function formatPrice($price) {
    return number_format($price, 2, ',', ' ') . ' ' . CURRENCY;
}

function calculateTVA($price_ht) {
    return $price_ht * (TVA_RATE / 100);
}

function calculateTTC($price_ht) {
    return $price_ht * (1 + TVA_RATE / 100);
}

function isAuthenticated() {
    return isset($_SESSION['user_id']);
}

function requireAuth() {
    if (!isAuthenticated()) {
        header('Location: /login.php');
        exit();
    }
}
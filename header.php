<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Système de gestion de pharmacie professionnel">
    <title>PharmaSys - Gestion de Pharmacie</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="/pharma/assets/img/favicon.png">
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <!-- CSS -->
    <link rel="stylesheet" href="/pharma/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/pharma/assets/css/style.css">
    <link rel="stylesheet" href="/pharma/assets/css/modal.css">
    <!-- JavaScript -->
    <!-- Bootstrap JavaScript moved to footer -->
</head>
<body>
<?php
require_once __DIR__ . '/auth.php';
requireAuth();
?>
<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container">
        <a class="navbar-brand" href="/pharma/">
            PharmaSys
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a href="/pharma/medicaments/" class="nav-link">
                        <i class="bi bi-capsule"></i> Médicaments
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/pharma/ventes/" class="nav-link">
                        <i class="bi bi-cart"></i> Ventes
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/pharma/commandes/" class="nav-link">
                        <i class="bi bi-box"></i> Commandes
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/pharma/fournisseurs/" class="nav-link">
                        <i class="bi bi-truck"></i> Fournisseurs
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/pharma/pharmaciens/" class="nav-link">
                        <i class="bi bi-people"></i> Pharmaciens
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/pharma/clients/" class="nav-link">
                        <i class="bi bi-person"></i> Clients
                    </a>
                </li>
            </ul>
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle"></i>
                        <?php echo htmlspecialchars($_SESSION['username']); ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="/pharma/profile.php">
                            <i class="bi bi-gear"></i> Paramètres
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="/pharma/logout.php">
                            <i class="bi bi-box-arrow-right"></i> Déconnexion
                        </a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container">
    <?php if(isset($_SESSION['message'])): ?>
        <div class="alert alert-<?php echo $_SESSION['message_type']; ?> fade-in">
            <?php 
                echo $_SESSION['message']; 
                unset($_SESSION['message']);
                unset($_SESSION['message_type']);
            ?>
        </div>
    <?php endif; ?>
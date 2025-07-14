<?php
require_once '../includes/db.php';
require_once __DIR__ . '/includes/auth.php';
session_start();

// Si l'utilisateur est déjà connecté, le rediriger vers la page d'accueil
if (isset($_SESSION['user_id'])) {
    header('Location: /pharma/');
    exit;
}

// Traitement du formulaire de connexion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $error = '';

    // Validation des champs
    if (empty($username)) {
        $error = 'Le nom d\'utilisateur est requis';
    } elseif (empty($password)) {
        $error = 'Le mot de passe est requis';
    } else {
        // Tentative de connexion
        if (login($username, $password)) {
            $_SESSION['message'] = 'Connexion réussie';
            $_SESSION['message_type'] = 'success';
            header('Location: /pharma/index.php');
            exit;
        } else {
            $error = 'Nom d\'utilisateur ou mot de passe incorrect';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Gestion de Pharmacie</title>
    <link rel="stylesheet" href="/pharma/assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="card" style="max-width: 400px; margin: 2rem auto;">
            <h1 style="text-align: center; margin-bottom: 2rem;">Connexion</h1>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <?php 
                        echo htmlspecialchars($_SESSION['error']);
                        unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="username" class="form-label">Nom d'utilisateur</label>
                    <input type="text" id="username" name="username" class="form-control" 
                           value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>" 
                           required autofocus>
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Mot de passe</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>

                <div class="form-group" style="text-align: center;">
                    <button type="submit" class="btn btn-primary" style="width: 100%;">Se connecter</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
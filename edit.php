<?php
session_start();
require_once '../config.php';
require_once '../includes/auth.php';

requireAuth();

// Vérifier si l'ID est fourni
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['message'] = 'ID du pharmacien non spécifié';
    $_SESSION['message_type'] = 'danger';
    header('Location: index.php');
    exit;
}

$id = $_GET['id'];

// Si le formulaire est soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validation des champs requis
        if (empty($_POST['nom']) || empty($_POST['prenom']) || empty($_POST['username'])) {
            throw new Exception('Les champs Nom, Prénom et Nom d\'utilisateur sont obligatoires');
        }

        // Vérifier si le username existe déjà pour un autre pharmacien
        $stmt = $pdo->prepare("SELECT id FROM pharmaciens WHERE username = ? AND id != ?");
        $stmt->execute([$_POST['username'], $id]);
        if ($stmt->fetch()) {
            throw new Exception('Ce nom d\'utilisateur est déjà utilisé');
        }

        // Préparer la requête de mise à jour
        $sql = "UPDATE pharmaciens SET 
                nom = :nom,
                prenom = :prenom,
                telephone = :telephone,
                email = :email,
                adresse = :adresse,
                date_embauche = :date_embauche,
                username = :username";

        // Ajouter le mot de passe à la requête s'il est fourni
        if (!empty($_POST['password'])) {
            $sql .= ", password = :password";
        }

        $sql .= " WHERE id = :id";
        
        $stmt = $pdo->prepare($sql);
        
        // Paramètres de base
        $params = [
            'nom' => $_POST['nom'],
            'prenom' => $_POST['prenom'],
            'telephone' => $_POST['telephone'],
            'email' => $_POST['email'],
            'adresse' => $_POST['adresse'],
            'date_embauche' => $_POST['date_embauche'],
            'username' => $_POST['username'],
            'id' => $id
        ];
        
        // Ajouter le mot de passe aux paramètres s'il est fourni
        if (!empty($_POST['password'])) {
            $params['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
        }
        
        $stmt->execute($params);
        
        $_SESSION['message'] = 'Pharmacien modifié avec succès';
        $_SESSION['message_type'] = 'success';
        header('Location: index.php');
        exit;
        
    } catch(Exception $e) {
        $_SESSION['message'] = 'Erreur lors de la modification : ' . $e->getMessage();
        $_SESSION['message_type'] = 'danger';
    }
}

// Récupérer les informations du pharmacien
try {
    $stmt = $pdo->prepare("SELECT * FROM pharmaciens WHERE id = ?");
    $stmt->execute([$id]);
    $pharmacien = $stmt->fetch();

    if (!$pharmacien) {
        $_SESSION['message'] = 'Pharmacien non trouvé';
        $_SESSION['message_type'] = 'danger';
        header('Location: index.php');
        exit;
    }
} catch(PDOException $e) {
    $_SESSION['message'] = 'Erreur lors de la récupération du pharmacien : ' . $e->getMessage();
    $_SESSION['message_type'] = 'danger';
    header('Location: index.php');
    exit;
}

require_once '../includes/header.php';
?>

<div class="container">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h2 mb-0">
                <i class="bi bi-pencil"></i> Modifier le Pharmacien
            </h1>
            <a href="index.php" class="btn btn-secondary mt-3">
                <i class="bi bi-arrow-left"></i> Retour à la liste
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
            <form method="post" class="needs-validation" novalidate>
                <div class="row">
                    <div class="col-md-6">
                        <h3 class="h4 mb-4">Informations Personnelles</h3>
                        
                        <div class="mb-3">
                            <label for="nom" class="form-label">Nom *</label>
                            <input type="text" class="form-control" id="nom" name="nom" 
                                   value="<?php echo htmlspecialchars($pharmacien['nom']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="prenom" class="form-label">Prénom *</label>
                            <input type="text" class="form-control" id="prenom" name="prenom" 
                                   value="<?php echo htmlspecialchars($pharmacien['prenom']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="date_embauche" class="form-label">Date d'embauche</label>
                            <input type="date" class="form-control" id="date_embauche" name="date_embauche" 
                                   value="<?php echo $pharmacien['date_embauche']; ?>">
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <h3 class="h4 mb-4">Contact</h3>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($pharmacien['email']); ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="telephone" class="form-label">Téléphone</label>
                            <input type="tel" class="form-control" id="telephone" name="telephone" 
                                   value="<?php echo htmlspecialchars($pharmacien['telephone']); ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="adresse" class="form-label">Adresse</label>
                            <textarea class="form-control" id="adresse" name="adresse" rows="3"><?php echo htmlspecialchars($pharmacien['adresse']); ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-6">
                        <h3 class="h4 mb-4">Informations de Connexion</h3>
                        
                        <div class="mb-3">
                            <label for="username" class="form-label">Nom d'utilisateur *</label>
                            <input type="text" class="form-control" id="username" name="username" 
                                   value="<?php echo htmlspecialchars($pharmacien['username']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Nouveau mot de passe</label>
                            <input type="password" class="form-control" id="password" name="password" 
                                   placeholder="Laissez vide pour ne pas modifier">
                            <div class="form-text">Minimum 8 caractères</div>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg"></i> Enregistrer les modifications
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
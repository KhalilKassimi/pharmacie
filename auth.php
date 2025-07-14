<?php
require_once __DIR__ . '/db.php';

// Constants
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_TIMEOUT_MINUTES', 15);

function tableExists($tableName) {
    global $pdo;
    try {
        $result = $pdo->query("SELECT 1 FROM {$tableName} LIMIT 1");
    } catch (Exception $e) {
        return false;
    }
    return $result !== false;
}

function requireAuth() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['error'] = 'Veuillez vous connecter pour accéder à cette page';
        header('Location: /pharma/login.php');
        exit;
    }

    // Check session timeout (2 hours)
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 7200)) {
        logout();
        $_SESSION['error'] = 'Session expirée. Veuillez vous reconnecter.';
        header('Location: /pharma/login.php');
        exit;
    }
    
    // Update last activity time
    $_SESSION['last_activity'] = time();
}

function isAuthenticated() {
    return isset($_SESSION['user_id']);
}

function checkLoginAttempts($username) {
    global $pdo;
    
    // If login_attempts table doesn't exist, allow login
    if (!tableExists('login_attempts')) {
        return true;
    }
    
    try {
        // Clean up old attempts
        $stmt = $pdo->prepare("DELETE FROM login_attempts WHERE attempt_time < DATE_SUB(NOW(), INTERVAL ? MINUTE)");
        $stmt->execute([LOGIN_TIMEOUT_MINUTES]);
        
        // Count recent attempts
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM login_attempts WHERE username = ? AND attempt_time > DATE_SUB(NOW(), INTERVAL ? MINUTE)");
        $stmt->execute([$username, LOGIN_TIMEOUT_MINUTES]);
        $attempts = $stmt->fetchColumn();
        
        return $attempts < MAX_LOGIN_ATTEMPTS;
    } catch (PDOException $e) {
        // If there's any error, allow login but log the error
        error_log("Error checking login attempts: " . $e->getMessage());
        return true;
    }
}

function recordLoginAttempt($username, $success) {
    global $pdo;
    
    // If login_attempts table doesn't exist, skip recording
    if (!tableExists('login_attempts')) {
        return;
    }
    
    try {
        $stmt = $pdo->prepare("INSERT INTO login_attempts (username, ip_address, success, attempt_time) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$username, $_SERVER['REMOTE_ADDR'], $success]);
        
        if ($success) {
            // Clear failed attempts on successful login
            $stmt = $pdo->prepare("DELETE FROM login_attempts WHERE username = ? AND success = 0");
            $stmt->execute([$username]);
        }
    } catch (PDOException $e) {
        // Log error but don't prevent login
        error_log("Error recording login attempt: " . $e->getMessage());
    }
}

function login($username, $password) {
    global $pdo;
    
    // Check for too many failed attempts
    if (!checkLoginAttempts($username)) {
        $_SESSION['error'] = sprintf(
            'Trop de tentatives de connexion. Veuillez réessayer dans %d minutes.', 
            LOGIN_TIMEOUT_MINUTES
        );
        return false;
    }
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            // Successful login
            session_regenerate_id(true); // Prevent session fixation
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['last_activity'] = time();
            
            // Record successful login
            recordLoginAttempt($username, true);
            
            // Update last login time if the column exists
            try {
                $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                $stmt->execute([$user['id']]);
            } catch (PDOException $e) {
                // Ignore if column doesn't exist
                error_log("Error updating last_login: " . $e->getMessage());
            }
            
            return true;
        }
        
        // Failed login
        recordLoginAttempt($username, false);
        return false;
    } catch (PDOException $e) {
        error_log("Error during login: " . $e->getMessage());
        $_SESSION['error'] = 'Erreur de connexion. Veuillez réessayer.';
        return false;
    }
}

function logout() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Record logout time if user was logged in
    if (isset($_SESSION['user_id'])) {
        global $pdo;
        try {
            $stmt = $pdo->prepare("UPDATE users SET last_logout = NOW() WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
        } catch (PDOException $e) {
            // Ignore if column doesn't exist
            error_log("Error updating last_logout: " . $e->getMessage());
        }
    }
    
    // Clear all session data
    $_SESSION = array();
    
    // Destroy the session cookie
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    
    session_destroy();
    header('Location: /pharma/login.php');
    exit;
}
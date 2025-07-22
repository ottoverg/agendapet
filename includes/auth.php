<?php
// Arquivo: /agendapet/includes/auth.php

// Verifica se o usuário está logado
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Redireciona para login se não estiver autenticado
function requireAuth() {
    if (!isLoggedIn()) {
        header('Location: ' . SITE_URL . '/login.php');
        exit();
    }
}

// Verifica se o usuário é admin
function isAdmin() {
    return isLoggedIn() && $_SESSION['user_role'] === 'admin';
}

// Requer privilégios de admin
function requireAdmin() {
    requireAuth();
    if (!isAdmin()) {
        header('Location: ' . SITE_URL . '/dashboard.php');
        exit();
    }
}

// Função de login
function login($email, $password) {
    global $pdo;
    
    $sql = "SELECT id, nome, email, senha, role FROM usuarios WHERE email = ?";
    $user = fetchOne($sql, [$email]);
    
    if ($user && password_verify($password, $user['senha'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['nome'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        return true;
    }
    
    return false;
}

// Função de logout
function logout() {
    session_unset();
    session_destroy();
}
?>
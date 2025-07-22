<?php
require_once 'includes/config.php';

// Se o usuário já estiver logado, redirecionar para o dashboard
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

// Caso contrário, redirecionar para a página de login
header('Location: login.php');
exit();
<?php
// Arquivo: /agendapet/includes/header.php

if (!defined('SITE_URL')) {
    define('SITE_URL', 'https://petsplace.net.br/agendapet');
}
if (!defined('ASSETS_PATH')) {
    define('ASSETS_PATH', SITE_URL . '/assets');
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' . SITE_NAME : SITE_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo ASSETS_PATH; ?>/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo ASSETS_PATH; ?>/css/styles.css">
    <link rel="stylesheet" href="<?php echo ASSETS_PATH; ?>/css/responsive.css">
    <link rel="shortcut icon" href="<?php echo ASSETS_PATH; ?>/img/logo.png" type="image/x-icon">
</head>
<body>
    <!-- Restante do conteúdo do header -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="https://petsplace.net.br/agendapet/index.php">
                <img src="<?php echo SITE_URL; ?>/assets/img/logo.png" alt="Pets Place" height="40">
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mr-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo SITE_URL; ?>/dashboard.php">Dashboard</a>
                    </li>
                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo SITE_URL; ?>/modules/agendamentos/novo.php">Novo Agendamento</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo SITE_URL; ?>/modules/clientes/listar.php">Clientes</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo SITE_URL; ?>/modules/pets/listar.php">Pets</a>
                        </li>
                        <?php if (isAdmin()): ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="adminDropdown" role="button" data-toggle="dropdown">
                                    Administração
                                </a>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="<?php echo SITE_URL; ?>/modules/usuarios/listar.php">Usuários</a>
                                    <a class="dropdown-item" href="<?php echo SITE_URL; ?>/modules/profissionais/listar.php">Profissionais</a>
                                    <a class="dropdown-item" href="<?php echo SITE_URL; ?>/modules/racas/listar.php">Raças</a>
                                    <a class="dropdown-item" href="<?php echo SITE_URL; ?>/modules/servicos/listar.php">Serviços</a>
                                </div>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo SITE_URL; ?>/modules/relatorios/mensal.php">Relatórios</a>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav">
                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown">
                                <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right">
                                <a class="dropdown-item" href="/agendapet/modules/usuarios/perfil.php">Perfil</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="<?php echo SITE_URL; ?>/logout.php">Sair</a>
                            </div>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo SITE_URL; ?>/login.php">Login</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container mt-4">
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?php echo $_SESSION['success_message']; ?>
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?php echo $_SESSION['error_message']; ?>
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>
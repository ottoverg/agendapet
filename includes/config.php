<?php
// Arquivo: /agendapet/includes/config.php

// Configurações do sistema
define('SITE_URL', 'https://petsplace.net.br/agendapet');
define('SITE_NAME', 'AgendaPet - Pets Place');

// Configurações do banco de dados
define('DB_HOST', 'localhost');
define('DB_NAME', 'ottove06_agendapet');
define('DB_USER', 'ottove06_ottoverg');
define('DB_PASS', '@Olnv183201#');

// Configurações de e-mail
define('SMTP_HOST', 'mail.petsplace.net.br');
define('SMTP_USER', 'comercial@petsplace.net.br');
define('SMTP_PASS', '@pl#Olnv183201#!!!');
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls');

// Timezone
date_default_timezone_set('America/Sao_Paulo');

// Inicia a sessão
session_start();

// Função para carregar classes automaticamente
spl_autoload_register(function ($class_name) {
    include_once __DIR__ . '/../classes/' . $class_name . '.php';
});

// Inclui o arquivo de conexão com o banco de dados
require_once __DIR__ . '/db.php';

// Inclui o arquivo de autenticação
require_once __DIR__ . '/auth.php';

?>
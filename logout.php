<?php
// Arquivo: /agendapet/logout.php

require_once 'includes/config.php';

logout();
header('Location: ' . SITE_URL . '/login.php');
exit();
?>
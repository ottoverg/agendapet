<?php
require_once '../../includes/config.php';
requireAdmin();

if (!isset($_GET['id'])) {
    header('Location: listar.php');
    exit();
}

$usuario_id = (int)$_GET['id'];

// Verificar se o usuário existe
$usuario = fetchOne("SELECT id FROM usuarios WHERE id = ?", [$usuario_id]);

if (!$usuario) {
    $_SESSION['error_message'] = 'Usuário não encontrado.';
    header('Location: listar.php');
    exit();
}

// Não permitir que o usuário exclua a si mesmo
if ($usuario_id == $_SESSION['user_id']) {
    $_SESSION['error_message'] = 'Você não pode excluir seu próprio usuário.';
    header('Location: listar.php');
    exit();
}

// Excluir o usuário
try {
    executeQuery("DELETE FROM usuarios WHERE id = ?", [$usuario_id]);
    $_SESSION['success_message'] = 'Usuário excluído com sucesso!';
} catch (PDOException $e) {
    $_SESSION['error_message'] = 'Erro ao excluir usuário: ' . $e->getMessage();
}

header('Location: listar.php');
exit();
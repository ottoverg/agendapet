<?php
require_once '../../includes/config.php';
requireAuth();

if (!isset($_GET['id'])) {
    header('Location: listar.php');
    exit();
}

$cliente_id = (int)$_GET['id'];

// Verificar se o cliente existe
$cliente = fetchOne("SELECT id FROM clientes WHERE id = ?", [$cliente_id]);

if (!$cliente) {
    $_SESSION['error_message'] = 'Cliente não encontrado.';
    header('Location: listar.php');
    exit();
}

// Verificar se há pets associados
$pets = fetchAll("SELECT id FROM pets WHERE cliente_id = ?", [$cliente_id]);

if (count($pets) > 0) {
    $_SESSION['error_message'] = 'Não é possível excluir este cliente pois existem pets cadastrados para ele.';
    header('Location: listar.php');
    exit();
}

// Verificar se há agendamentos associados
$agendamentos = fetchAll("SELECT id FROM agendamentos WHERE cliente_id = ?", [$cliente_id]);

if (count($agendamentos) > 0) {
    $_SESSION['error_message'] = 'Não é possível excluir este cliente pois existem agendamentos associados a ele.';
    header('Location: listar.php');
    exit();
}

// Excluir o cliente
try {
    executeQuery("DELETE FROM clientes WHERE id = ?", [$cliente_id]);
    $_SESSION['success_message'] = 'Cliente excluído com sucesso!';
} catch (PDOException $e) {
    $_SESSION['error_message'] = 'Erro ao excluir cliente: ' . $e->getMessage();
}

header('Location: listar.php');
exit();
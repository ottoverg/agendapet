<?php
require_once '../../includes/config.php';
requireAuth();

if (!isset($_GET['id'])) {
    header('Location: listar.php');
    exit();
}

$agendamento_id = (int)$_GET['id'];

// Verificar se o agendamento existe
$agendamento = fetchOne("SELECT id FROM agendamentos WHERE id = ?", [$agendamento_id]);

if (!$agendamento) {
    $_SESSION['error_message'] = 'Agendamento não encontrado.';
    header('Location: listar.php');
    exit();
}

// Excluir o agendamento
try {
    executeQuery("DELETE FROM agendamentos WHERE id = ?", [$agendamento_id]);
    $_SESSION['success_message'] = 'Agendamento excluído com sucesso!';
} catch (PDOException $e) {
    $_SESSION['error_message'] = 'Erro ao excluir agendamento: ' . $e->getMessage();
}

header('Location: listar.php');
exit();
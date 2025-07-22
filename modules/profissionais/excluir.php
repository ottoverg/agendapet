<?php
require_once '../../includes/config.php';
requireAdmin();

if (!isset($_GET['id'])) {
    header('Location: listar.php');
    exit();
}

$profissional_id = (int)$_GET['id'];

// Verificar se o profissional existe
$profissional = fetchOne("SELECT id FROM profissionais WHERE id = ?", [$profissional_id]);

if (!$profissional) {
    $_SESSION['error_message'] = 'Profissional não encontrado.';
    header('Location: listar.php');
    exit();
}

// Verificar se há agendamentos associados
$agendamentos = fetchAll("SELECT id FROM agendamentos WHERE profissional_id = ?", [$profissional_id]);

if (count($agendamentos) > 0) {
    $_SESSION['error_message'] = 'Não é possível excluir este profissional pois existem agendamentos associados a ele.';
    header('Location: listar.php');
    exit();
}

// Verificar se há comissões associadas
$comissoes = fetchAll("SELECT id FROM profissionais_comissoes WHERE profissional_id = ?", [$profissional_id]);

if (count($comissoes) > 0) {
    // Primeiro excluir as comissões
    try {
        executeQuery("DELETE FROM profissionais_comissoes WHERE profissional_id = ?", [$profissional_id]);
    } catch (PDOException $e) {
        $_SESSION['error_message'] = 'Erro ao excluir comissões do profissional: ' . $e->getMessage();
        header('Location: listar.php');
        exit();
    }
}

// Excluir o profissional
try {
    executeQuery("DELETE FROM profissionais WHERE id = ?", [$profissional_id]);
    $_SESSION['success_message'] = 'Profissional excluído com sucesso!';
} catch (PDOException $e) {
    $_SESSION['error_message'] = 'Erro ao excluir profissional: ' . $e->getMessage();
}

header('Location: listar.php');
exit();
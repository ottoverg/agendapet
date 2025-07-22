<?php
require_once '../../includes/config.php';
requireAdmin();

if (!isset($_GET['id'])) {
    header('Location: listar.php');
    exit();
}

$servico_id = (int)$_GET['id'];

// Verificar se o serviço existe
$servico = fetchOne("SELECT id FROM servicos WHERE id = ?", [$servico_id]);

if (!$servico) {
    $_SESSION['error_message'] = 'Serviço não encontrado.';
    header('Location: listar.php');
    exit();
}

// Verificar se há agendamentos associados a este serviço
$agendamentos = fetchAll("SELECT id FROM agendamentos WHERE servico_id = ?", [$servico_id]);

if (count($agendamentos) > 0) {
    $_SESSION['error_message'] = 'Não é possível excluir este serviço pois existem agendamentos associados a ele.';
    header('Location: listar.php');
    exit();
}

// Verificar se há comissões associadas a este serviço
$comissoes = fetchAll("SELECT id FROM profissionais_comissoes WHERE servico_id = ?", [$servico_id]);

if (count($comissoes) > 0) {
    // Primeiro excluir as comissões
    try {
        executeQuery("DELETE FROM profissionais_comissoes WHERE servico_id = ?", [$servico_id]);
    } catch (PDOException $e) {
        $_SESSION['error_message'] = 'Erro ao excluir comissões do serviço: ' . $e->getMessage();
        header('Location: listar.php');
        exit();
    }
}

// Excluir o serviço
try {
    executeQuery("DELETE FROM servicos WHERE id = ?", [$servico_id]);
    $_SESSION['success_message'] = 'Serviço excluído com sucesso!';
} catch (PDOException $e) {
    $_SESSION['error_message'] = 'Erro ao excluir serviço: ' . $e->getMessage();
}

header('Location: listar.php');
exit();
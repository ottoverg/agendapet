<?php
require_once '../../includes/config.php';
requireAuth();

if (!isset($_GET['id'])) {
    header('Location: listar.php');
    exit();
}

$pet_id = (int)$_GET['id'];

// Verificar se o pet existe
$pet = fetchOne("SELECT id FROM pets WHERE id = ?", [$pet_id]);

if (!$pet) {
    $_SESSION['error_message'] = 'Pet não encontrado.';
    header('Location: listar.php');
    exit();
}

// Verificar se há agendamentos associados
$agendamentos = fetchAll("SELECT id FROM agendamentos WHERE pet_id = ?", [$pet_id]);

if (count($agendamentos) > 0) {
    $_SESSION['error_message'] = 'Não é possível excluir este pet pois existem agendamentos associados a ele.';
    header('Location: listar.php');
    exit();
}

// Excluir o pet
try {
    executeQuery("DELETE FROM pets WHERE id = ?", [$pet_id]);
    $_SESSION['success_message'] = 'Pet excluído com sucesso!';
} catch (PDOException $e) {
    $_SESSION['error_message'] = 'Erro ao excluir pet: ' . $e->getMessage();
}

header('Location: listar.php');
exit();
<?php
require_once '../../includes/config.php';
requireAdmin();

if (!isset($_GET['id'])) {
    header('Location: listar.php');
    exit();
}

$raca_id = (int)$_GET['id'];

// Verificar se a raça existe
$raca = fetchOne("SELECT id FROM racas WHERE id = ?", [$raca_id]);

if (!$raca) {
    $_SESSION['error_message'] = 'Raça não encontrada.';
    header('Location: listar.php');
    exit();
}

// Verificar se há pets associados a esta raça
$pets = fetchAll("SELECT id FROM pets WHERE raca_id = ?", [$raca_id]);

if (count($pets) > 0) {
    $_SESSION['error_message'] = 'Não é possível excluir esta raça pois existem pets cadastrados com ela.';
    header('Location: listar.php');
    exit();
}

// Excluir a raça
try {
    executeQuery("DELETE FROM racas WHERE id = ?", [$raca_id]);
    $_SESSION['success_message'] = 'Raça excluída com sucesso!';
} catch (PDOException $e) {
    $_SESSION['error_message'] = 'Erro ao excluir raça: ' . $e->getMessage();
}

header('Location: listar.php');
exit();
<?php
require_once '../../includes/config.php';
requireAuth();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);
    
    if ($id && $status) {
        try {
            $sql = "UPDATE agendamentos SET status = ? WHERE id = ?";
            executeQuery($sql, [$status, $id]);
            
            echo json_encode(['success' => true]);
            exit();
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao atualizar status']);
            exit();
        }
    }
}

echo json_encode(['success' => false, 'message' => 'Requisição inválida']);
?>
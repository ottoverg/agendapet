<?php
// Arquivo: /agendapet/modules/agendamentos/check_disponibilidade.php

require_once '../../../includes/config.php';
requireAuth();

header('Content-Type: application/json');

$profissional_id = isset($_GET['profissional_id']) ? (int)$_GET['profissional_id'] : 0;
$data_agendamento = isset($_GET['data_agendamento']) ? $_GET['data_agendamento'] : '';
$hora = isset($_GET['hora']) ? $_GET['hora'] : '';
$agendamento_id = isset($_GET['agendamento_id']) ? (int)$_GET['agendamento_id'] : 0;

if ($profissional_id && $data_agendamento && $hora) {
    $sql = "SELECT id FROM agendamentos 
            WHERE profissional_id = ? 
            AND data_agendamento = ? 
            AND hora = ? 
            AND id != ?";
    $existing = fetchOne($sql, [$profissional_id, $data_agendamento, $hora, $agendamento_id]);
    
    echo json_encode(['disponivel' => !$existing]);
} else {
    echo json_encode(['disponivel' => false]);
}
?>
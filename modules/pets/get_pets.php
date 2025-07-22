<?php
// Arquivo: /agendapet/modules/pets/get_pets.php

require_once '../../../includes/config.php';
requireAuth();

header('Content-Type: application/json');

$cliente_id = isset($_GET['cliente_id']) ? (int)$_GET['cliente_id'] : 0;

if ($cliente_id) {
    $sql = "SELECT id, nome FROM pets WHERE cliente_id = ? ORDER BY nome";
    $pets = fetchAll($sql, [$cliente_id]);
    echo json_encode($pets);
} else {
    echo json_encode([]);
}
?>
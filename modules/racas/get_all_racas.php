<?php
require_once '../../includes/config.php';

header('Content-Type: application/json');

try {
    $stmt = $pdo->query("SELECT especie, id, nome FROM racas ORDER BY especie, nome");
    $racas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $resultado = [];
    foreach($racas as $raca) {
        if(!isset($resultado[$raca['especie']])) {
            $resultado[$raca['especie']] = [];
        }
        $resultado[$raca['especie']][] = [
            'id' => $raca['id'],
            'nome' => $raca['nome']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'data' => $resultado
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
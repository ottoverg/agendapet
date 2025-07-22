<?php
require_once '../../includes/config.php';

// Ativar exibição de erros para debug (remover em produção)
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

try {
    if (!isset($_GET['especie'])) {
        throw new Exception('Parâmetro "especie" não fornecido');
    }

    $especie = filter_input(INPUT_GET, 'especie', FILTER_SANITIZE_STRING);
    
    // Verificar se a espécie é válida
    if (!in_array($especie, ['Cachorro', 'Gato'])) {
        throw new Exception('Espécie inválida');
    }

    // Preparar e executar a consulta
    $stmt = $pdo->prepare("SELECT id, nome FROM racas WHERE especie = ? ORDER BY nome");
    $stmt->execute([$especie]);
    $racas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Log para debug (remova em produção)
    file_put_contents('racas_debug.log', date('Y-m-d H:i:s')." - Espécie: $especie\n".print_r($racas, true)."\n\n", FILE_APPEND);

    // Retornar resposta
    echo json_encode([
        'success' => true,
        'data' => $racas,
        'count' => count($racas)
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Erro no banco de dados: ' . $e->getMessage(),
        'trace' => $e->getTraceAsString() // Remover em produção
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
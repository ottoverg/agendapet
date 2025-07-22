<?php
require_once '../../includes/config.php';
requireAdmin();

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=agendamentos_' . date('Y-m-d') . '.csv');

$output = fopen('php://output', 'w');

// Cabeçalho do CSV
fputcsv($output, [
    'ID',
    'Data',
    'Hora',
    'Cliente',
    'Pet',
    'Serviço',
    'Profissional',
    'Status',
    'Valor (R$)'
], ';');

// Filtros
$data_inicio = isset($_GET['data_inicio']) ? $_GET['data_inicio'] : date('Y-m-01');
$data_fim = isset($_GET['data_fim']) ? $_GET['data_fim'] : date('Y-m-t');
$status = isset($_GET['status']) ? $_GET['status'] : '';

// Consulta
$sql = "SELECT 
        a.id,
        a.data_agendamento,
        a.hora,
        c.nome as cliente,
        p.nome as pet,
        s.nome as servico,
        pr.nome as profissional,
        a.status,
        s.preco
        FROM agendamentos a
        JOIN clientes c ON a.cliente_id = c.id
        JOIN pets p ON a.pet_id = p.id
        JOIN servicos s ON a.servico_id = s.id
        JOIN profissionais pr ON a.profissional_id = pr.id
        WHERE a.data_agendamento BETWEEN ? AND ?";

$params = [$data_inicio, $data_fim];

if ($status) {
    $sql .= " AND a.status = ?";
    $params[] = $status;
}

$sql .= " ORDER BY a.data_agendamento, a.hora";

$agendamentos = fetchAll($sql, $params);

foreach ($agendamentos as $ag) {
    fputcsv($output, [
        $ag['id'],
        date('d/m/Y', strtotime($ag['data_agendamento'])),
        substr($ag['hora'], 0, 5),
        $ag['cliente'],
        $ag['pet'],
        $ag['servico'],
        $ag['profissional'],
        ucfirst($ag['status']),
        number_format($ag['preco'], 2, ',', '.')
    ], ';');
}

fclose($output);
exit();
?>
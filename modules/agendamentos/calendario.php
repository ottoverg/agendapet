<?php
require_once '../../../includes/config.php';
requireAuth();

header('Content-Type: application/json');

$start = isset($_GET['start']) ? $_GET['start'] : date('Y-m-01');
$end = isset($_GET['end']) ? $_GET['end'] : date('Y-m-t');

$sql = "SELECT 
        a.id, 
        CONCAT(c.nome, ' - ', p.nome) as title,
        a.data_agendamento as start,
        CONCAT(a.data_agendamento, 'T', a.hora) as start_time,
        CONCAT(a.data_agendamento, 'T', a.hora_final) as end_time,
        s.nome as servico,
        pr.nome as profissional,
        a.status,
        CASE 
            WHEN a.status = 'agendado' THEN '#3788d8'
            WHEN a.status = 'confirmado' THEN '#5cb85c'
            WHEN a.status = 'concluido' THEN '#5bc0de'
            WHEN a.status = 'cancelado' THEN '#d9534f'
            ELSE '#f0ad4e'
        END as color
        FROM agendamentos a
        JOIN clientes c ON a.cliente_id = c.id
        JOIN pets p ON a.pet_id = p.id
        JOIN servicos s ON a.servico_id = s.id
        JOIN profissionais pr ON a.profissional_id = pr.id
        WHERE a.data_agendamento BETWEEN ? AND ?
        ORDER BY a.data_agendamento, a.hora";

$agendamentos = fetchAll($sql, [$start, $end]);

$events = [];
foreach ($agendamentos as $ag) {
    $events[] = [
        'id' => $ag['id'],
        'title' => $ag['title'] . ' (' . $ag['servico'] . ')',
        'start' => $ag['start_time'],
        'end' => $ag['end_time'],
        'color' => $ag['color'],
        'extendedProps' => [
            'profissional' => $ag['profissional'],
            'status' => $ag['status']
        ]
    ];
}

echo json_encode($events);
?>
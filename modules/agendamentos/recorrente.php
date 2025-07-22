<?php
require_once '../../includes/config.php';
requireAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: listar.php');
    exit();
}

$agendamento_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$recorrencia = isset($_POST['recorrencia']) ? $_POST['recorrencia'] : '';
$recorrencia_fim = isset($_POST['recorrencia_fim']) ? $_POST['recorrencia_fim'] : '';

// Validar dados
if (!$agendamento_id || !$recorrencia || !$recorrencia_fim) {
    $_SESSION['error_message'] = 'Dados inválidos para criar recorrência.';
    header('Location: listar.php');
    exit();
}

// Obter agendamento original
$sql = "SELECT * FROM agendamentos WHERE id = ?";
$original = fetchOne($sql, [$agendamento_id]);

if (!$original) {
    $_SESSION['error_message'] = 'Agendamento não encontrado.';
    header('Location: listar.php');
    exit();
}

// Criar agendamentos recorrentes
$start_date = new DateTime($original['data_agendamento']);
$end_date = new DateTime($recorrencia_fim);
$interval = null;

switch ($recorrencia) {
    case 'semanal':
        $interval = new DateInterval('P1W');
        break;
    case 'quinzenal':
        $interval = new DateInterval('P2W');
        break;
    case 'mensal':
        $interval = new DateInterval('P1M');
        break;
}

if ($interval) {
    $period = new DatePeriod($start_date, $interval, $end_date);
    $count = 0;
    
    foreach ($period as $date) {
        if ($date == $start_date) continue; // Pular o original
        
        $rec_data = $date->format('Y-m-d');
        
        // Verificar disponibilidade
        $sql_check = "SELECT id FROM agendamentos 
                     WHERE profissional_id = ? 
                     AND data_agendamento = ? 
                     AND hora = ?";
        $existing = fetchOne($sql_check, [
            $original['profissional_id'],
            $rec_data,
            $original['hora']
        ]);
        
        if (!$existing) {
            // Inserir agendamento recorrente
            $sql_insert = "INSERT INTO agendamentos 
                          (cliente_id, pet_id, servico_id, profissional_id, 
                           data_agendamento, hora, hora_final, observacoes, status, data_criacao)
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            
            executeQuery($sql_insert, [
                $original['cliente_id'],
                $original['pet_id'],
                $original['servico_id'],
                $original['profissional_id'],
                $rec_data,
                $original['hora'],
                $original['hora_final'],
                $original['observacoes'],
                $original['status']
            ]);
            
            $count++;
        }
    }
    
    $_SESSION['success_message'] = "Foram criados $count agendamentos recorrentes.";
} else {
    $_SESSION['error_message'] = 'Tipo de recorrência inválido.';
}

header('Location: listar.php');
exit();
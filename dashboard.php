<?php
// Arquivo: /agendapet/dashboard.php

require_once 'includes/config.php';
requireAuth();

$page_title = 'Dashboard';
include 'includes/header.php';

// Consulta para agendamentos do dia
$today = date('Y-m-d');
$sql = "SELECT a.id, a.data_agendamento, a.hora, c.nome as cliente_nome, p.nome as pet_nome, s.nome as servico_nome
        FROM agendamentos a
        JOIN clientes c ON a.cliente_id = c.id
        JOIN pets p ON a.pet_id = p.id
        JOIN servicos s ON a.servico_id = s.id
        WHERE a.data_agendamento = ?
        ORDER BY a.hora ASC";
$agendamentos_hoje = fetchAll($sql, [$today]);

// Consulta para próximos agendamentos
$sql = "SELECT a.id, a.data_agendamento, a.hora, c.nome as cliente_nome, p.nome as pet_nome, s.nome as servico_nome
        FROM agendamentos a
        JOIN clientes c ON a.cliente_id = c.id
        JOIN pets p ON a.pet_id = p.id
        JOIN servicos s ON a.servico_id = s.id
        WHERE a.data_agendamento > ?
        ORDER BY a.data_agendamento ASC, a.hora ASC
        LIMIT 5";
$proximos_agendamentos = fetchAll($sql, [$today]);

// Estatísticas
$sql = "SELECT COUNT(*) as total FROM clientes";
$total_clientes = fetchOne($sql)['total'];

$sql = "SELECT COUNT(*) as total FROM pets";
$total_pets = fetchOne($sql)['total'];

$sql = "SELECT COUNT(*) as total FROM agendamentos WHERE data_agendamento = ?";
$total_agendamentos_hoje = fetchOne($sql, [$today])['total'];

$sql = "SELECT COUNT(*) as total FROM agendamentos WHERE data_agendamento > ? AND data_agendamento <= ?";
$proxima_semana = date('Y-m-d', strtotime('+7 days'));
$total_agendamentos_semana = fetchOne($sql, [$today, $proxima_semana])['total'];
?>

<div class="row">
    <div class="col-md-3 mb-4">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h5 class="card-title">Clientes</h5>
                <h2 class="card-text"><?php echo $total_clientes; ?></h2>
                <a href="<?php echo SITE_URL; ?>/modules/clientes/listar.php" class="text-white">Ver todos</a>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h5 class="card-title">Pets</h5>
                <h2 class="card-text"><?php echo $total_pets; ?></h2>
                <a href="<?php echo SITE_URL; ?>/modules/pets/listar.php" class="text-white">Ver todos</a>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card bg-info text-white">
            <div class="card-body">
                <h5 class="card-title">Agend. Hoje</h5>
                <h2 class="card-text"><?php echo $total_agendamentos_hoje; ?></h2>
                <a href="<?php echo SITE_URL; ?>/modules/agendamentos/listar.php?data=<?php echo $today; ?>" class="text-white">Ver agenda</a>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card bg-warning text-dark">
            <div class="card-body">
                <h5 class="card-title">Agend. Semana</h5>
                <h2 class="card-text"><?php echo $total_agendamentos_semana; ?></h2>
                <a href="<?php echo SITE_URL; ?>/modules/agendamentos/listar.php?data_inicio=<?php echo $today; ?>&data_fim=<?php echo $proxima_semana; ?>" class="text-dark">Ver agenda</a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>Agendamentos de Hoje (<?php echo date('d/m/Y'); ?>)</h5>
            </div>
            <div class="card-body">
                <?php if (count($agendamentos_hoje) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Hora</th>
                                    <th>Cliente</th>
                                    <th>Pet</th>
                                    <th>Serviço</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($agendamentos_hoje as $agendamento): ?>
                                    <tr>
                                        <td><?php echo substr($agendamento['hora'], 0, 5); ?></td>
                                        <td><?php echo htmlspecialchars($agendamento['cliente_nome']); ?></td>
                                        <td><?php echo htmlspecialchars($agendamento['pet_nome']); ?></td>
                                        <td><?php echo htmlspecialchars($agendamento['servico_nome']); ?></td>
                                        <td>
                                            <a href="<?php echo SITE_URL; ?>/modules/agendamentos/editar.php?id=<?php echo $agendamento['id']; ?>" class="btn btn-sm btn-primary">Editar</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p>Nenhum agendamento para hoje.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>Próximos Agendamentos</h5>
            </div>
            <div class="card-body">
                <?php if (count($proximos_agendamentos) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Hora</th>
                                    <th>Cliente</th>
                                    <th>Pet</th>
                                    <th>Serviço</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($proximos_agendamentos as $agendamento): ?>
                                    <tr>
                                        <td><?php echo date('d/m/Y', strtotime($agendamento['data_agendamento'])); ?></td>
                                        <td><?php echo substr($agendamento['hora'], 0, 5); ?></td>
                                        <td><?php echo htmlspecialchars($agendamento['cliente_nome']); ?></td>
                                        <td><?php echo htmlspecialchars($agendamento['pet_nome']); ?></td>
                                        <td><?php echo htmlspecialchars($agendamento['servico_nome']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p>Nenhum agendamento futuro.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
<?php
require_once '../includes/config.php';
requireAuth();

$page_title = 'Relatório Diário';
include '../includes/header.php';

// Data padrão é hoje
$data = isset($_GET['data']) ? $_GET['data'] : date('Y-m-d');

// Obter agendamentos do dia
$sql = "SELECT a.id, a.hora, a.status, 
               c.nome as cliente, p.nome as pet, 
               s.nome as servico, s.preco,
               pr.nome as profissional
        FROM agendamentos a
        JOIN clientes c ON a.cliente_id = c.id
        JOIN pets p ON a.pet_id = p.id
        JOIN servicos s ON a.servico_id = s.id
        JOIN profissionais pr ON a.profissional_id = pr.id
        WHERE a.data_agendamento = ?
        ORDER BY a.hora";

$agendamentos = fetchAll($sql, [$data]);

// Calcular totais
$total_agendamentos = count($agendamentos);
$total_valor = array_sum(array_column($agendamentos, 'preco'));
?>

<div class="container mt-4">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <div class="d-flex justify-content-between align-items-center">
                <h4>Relatório Diário - <?php echo date('d/m/Y', strtotime($data)); ?></h4>
                <form method="GET" class="form-inline">
                    <input type="date" class="form-control mr-2" name="data" value="<?php echo $data; ?>">
                    <button type="submit" class="btn btn-light">Filtrar</button>
                </form>
            </div>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h5>Total Agendamentos</h5>
                            <h3><?php echo $total_agendamentos; ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h5>Valor Total</h5>
                            <h3>R$ <?php echo number_format($total_valor, 2, ',', '.'); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h5>Ticket Médio</h5>
                            <h3>R$ <?php echo $total_agendamentos > 0 ? number_format($total_valor/$total_agendamentos, 2, ',', '.') : '0,00'; ?></h3>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($agendamentos): ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Hora</th>
                                <th>Cliente</th>
                                <th>Pet</th>
                                <th>Serviço</th>
                                <th>Profissional</th>
                                <th>Valor</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($agendamentos as $agendamento): ?>
                                <tr>
                                    <td><?php echo substr($agendamento['hora'], 0, 5); ?></td>
                                    <td><?php echo htmlspecialchars($agendamento['cliente']); ?></td>
                                    <td><?php echo htmlspecialchars($agendamento['pet']); ?></td>
                                    <td><?php echo htmlspecialchars($agendamento['servico']); ?></td>
                                    <td><?php echo htmlspecialchars($agendamento['profissional']); ?></td>
                                    <td>R$ <?php echo number_format($agendamento['preco'], 2, ',', '.'); ?></td>
                                    <td>
                                        <span class="badge badge-<?php 
                                            echo $agendamento['status'] == 'concluido' ? 'success' : 
                                                 ($agendamento['status'] == 'cancelado' ? 'danger' : 'info'); 
                                        ?>">
                                            <?php echo ucfirst($agendamento['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-3">
                    <button class="btn btn-success" onclick="window.print()">
                        <i class="fas fa-print"></i> Imprimir Relatório
                    </button>
                    <button class="btn btn-info" id="exportExcel">
                        <i class="fas fa-file-excel"></i> Exportar para Excel
                    </button>
                </div>
            <?php else: ?>
                <div class="alert alert-info">Nenhum agendamento encontrado para esta data.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Exportar para Excel
    $('#exportExcel').click(function() {
        // Implementar lógica de exportação para Excel
        alert('Exportar para Excel será implementado aqui.');
    });
});
</script>

<?php include '../includes/footer.php'; ?>
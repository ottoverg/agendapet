<?php
require_once '../includes/config.php';
requireAuth();

$page_title = 'Relatório Personalizado';
include '../includes/header.php';

// Valores padrão
$data_inicio = isset($_GET['data_inicio']) ? $_GET['data_inicio'] : date('Y-m-01');
$data_fim = isset($_GET['data_fim']) ? $_GET['data_fim'] : date('Y-m-t');
$profissional_id = isset($_GET['profissional_id']) ? (int)$_GET['profissional_id'] : 0;
$servico_id = isset($_GET['servico_id']) ? (int)$_GET['servico_id'] : 0;
$status = isset($_GET['status']) ? $_GET['status'] : '';

// Obter profissionais e serviços para os selects
$profissionais = fetchAll("SELECT id, nome FROM profissionais ORDER BY nome");
$servicos = fetchAll("SELECT id, nome FROM servicos ORDER BY nome");

// Construir a query com filtros
$where = ["a.data_agendamento BETWEEN ? AND ?"];
$params = [$data_inicio, $data_fim];

if ($profissional_id) {
    $where[] = "a.profissional_id = ?";
    $params[] = $profissional_id;
}

if ($servico_id) {
    $where[] = "a.servico_id = ?";
    $params[] = $servico_id;
}

if ($status) {
    $where[] = "a.status = ?";
    $params[] = $status;
}

$where_clause = $where ? "WHERE " . implode(" AND ", $where) : "";

// Obter agendamentos filtrados
$sql = "SELECT a.id, a.data_agendamento, a.hora, a.status, 
               c.nome as cliente, p.nome as pet, 
               s.nome as servico, s.preco,
               pr.nome as profissional
        FROM agendamentos a
        JOIN clientes c ON a.cliente_id = c.id
        JOIN pets p ON a.pet_id = p.id
        JOIN servicos s ON a.servico_id = s.id
        JOIN profissionais pr ON a.profissional_id = pr.id
        $where_clause
        ORDER BY a.data_agendamento, a.hora";

$agendamentos = fetchAll($sql, $params);

// Calcular totais
$total_agendamentos = count($agendamentos);
$total_valor = array_sum(array_column($agendamentos, 'preco'));
?>

<div class="container mt-4">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h4>Relatório Personalizado</h4>
        </div>
        <div class="card-body">
            <form method="GET" class="mb-4">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="data_inicio">Data Início</label>
                            <input type="date" class="form-control" id="data_inicio" name="data_inicio" value="<?php echo $data_inicio; ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="data_fim">Data Fim</label>
                            <input type="date" class="form-control" id="data_fim" name="data_fim" value="<?php echo $data_fim; ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="profissional_id">Profissional</label>
                            <select class="form-control" id="profissional_id" name="profissional_id">
                                <option value="">Todos</option>
                                <?php foreach ($profissionais as $prof): ?>
                                    <option value="<?php echo $prof['id']; ?>" <?php echo $prof['id'] == $profissional_id ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($prof['nome']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="servico_id">Serviço</label>
                            <select class="form-control" id="servico_id" name="servico_id">
                                <option value="">Todos</option>
                                <?php foreach ($servicos as $serv): ?>
                                    <option value="<?php echo $serv['id']; ?>" <?php echo $serv['id'] == $servico_id ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($serv['nome']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select class="form-control" id="status" name="status">
                                <option value="">Todos</option>
                                <option value="agendado" <?php echo $status == 'agendado' ? 'selected' : ''; ?>>Agendado</option>
                                <option value="concluido" <?php echo $status == 'concluido' ? 'selected' : ''; ?>>Concluído</option>
                                <option value="cancelado" <?php echo $status == 'cancelado' ? 'selected' : ''; ?>>Cancelado</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-9 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary mr-2">
                            <i class="fas fa-filter"></i> Filtrar
                        </button>
                        <a href="personalizado.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Limpar
                        </a>
                    </div>
                </div>
            </form>

            <?php if ($agendamentos): ?>
                <div class="alert alert-info">
                    <strong>Período:</strong> <?php echo date('d/m/Y', strtotime($data_inicio)); ?> a <?php echo date('d/m/Y', strtotime($data_fim)); ?>
                    | <strong>Total:</strong> <?php echo $total_agendamentos; ?> agendamentos
                    | <strong>Valor Total:</strong> R$ <?php echo number_format($total_valor, 2, ',', '.'); ?>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Data</th>
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
                                    <td><?php echo date('d/m/Y', strtotime($agendamento['data_agendamento'])); ?></td>
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
                <div class="alert alert-info">Nenhum agendamento encontrado com os filtros selecionados.</div>
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
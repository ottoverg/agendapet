<?php
require_once '../includes/config.php';
requireAdmin();

$page_title = 'Relatório Semanal';
$custom_js = 'relatorios.js';
include '../includes/header.php';

// Definir data padrão (semana atual)
$data_inicio = isset($_GET['data_inicio']) ? $_GET['data_inicio'] : date('Y-m-d', strtotime('monday this week'));
$data_fim = isset($_GET['data_fim']) ? $_GET['data_fim'] : date('Y-m-d', strtotime('sunday this week'));

// Obter dados para o relatório
$sql = "SELECT 
            a.data_agendamento,
            DAYNAME(a.data_agendamento) as dia_semana,
            COUNT(a.id) as total_agendamentos,
            SUM(s.preco) as faturamento_total,
            GROUP_CONCAT(DISTINCT p.nome SEPARATOR ', ') as profissionais
        FROM agendamentos a
        JOIN servicos s ON a.servico_id = s.id
        JOIN profissionais p ON a.profissional_id = p.id
        WHERE a.data_agendamento BETWEEN ? AND ?
        GROUP BY a.data_agendamento
        ORDER BY a.data_agendamento";

$dados_semanais = fetchAll($sql, [$data_inicio, $data_fim]);

// Calcular totais
$total_agendamentos = 0;
$total_faturamento = 0;

if ($dados_semanais) {
    $total_agendamentos = array_sum(array_column($dados_semanais, 'total_agendamentos'));
    $total_faturamento = array_sum(array_column($dados_semanais, 'faturamento_total'));
}

// Obter serviços mais agendados
$sql_servicos = "SELECT 
                    s.nome as servico,
                    COUNT(a.id) as total,
                    SUM(s.preco) as faturamento
                FROM agendamentos a
                JOIN servicos s ON a.servico_id = s.id
                WHERE a.data_agendamento BETWEEN ? AND ?
                GROUP BY s.nome
                ORDER BY total DESC
                LIMIT 5";

$servicos_mais_agendados = fetchAll($sql_servicos, [$data_inicio, $data_fim]);

// Obter profissionais mais ocupados
$sql_profissionais = "SELECT 
                        p.nome as profissional,
                        COUNT(a.id) as total_atendimentos,
                        SUM(s.preco) as faturamento
                    FROM agendamentos a
                    JOIN profissionais p ON a.profissional_id = p.id
                    JOIN servicos s ON a.servico_id = s.id
                    WHERE a.data_agendamento BETWEEN ? AND ?
                    GROUP BY p.nome
                    ORDER BY total_atendimentos DESC
                    LIMIT 5";

$profissionais_mais_ocupados = fetchAll($sql_profissionais, [$data_inicio, $data_fim]);
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <h2><i class="fas fa-chart-bar"></i> Relatório Semanal</h2>
            <hr>
            
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="form-inline">
                        <div class="form-group mr-3">
                            <label for="data_inicio" class="mr-2">Data Início:</label>
                            <input type="date" class="form-control" id="data_inicio" name="data_inicio" value="<?php echo htmlspecialchars($data_inicio); ?>" required>
                        </div>
                        <div class="form-group mr-3">
                            <label for="data_fim" class="mr-2">Data Fim:</label>
                            <input type="date" class="form-control" id="data_fim" name="data_fim" value="<?php echo htmlspecialchars($data_fim); ?>" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Gerar Relatório</button>
                    </form>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Resumo Semanal</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card bg-light mb-3">
                                <div class="card-body text-center">
                                    <h5 class="card-title">Período</h5>
                                    <p class="card-text h4">
                                        <?php echo date('d/m/Y', strtotime($data_inicio)); ?> a <?php echo date('d/m/Y', strtotime($data_fim)); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light mb-3">
                                <div class="card-body text-center">
                                    <h5 class="card-title">Total Agendamentos</h5>
                                    <p class="card-text h4"><?php echo $total_agendamentos; ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light mb-3">
                                <div class="card-body text-center">
                                    <h5 class="card-title">Faturamento Total</h5>
                                    <p class="card-text h4">R$ <?php echo number_format($total_faturamento, 2, ',', '.'); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5>Agendamentos por Dia</h5>
                        </div>
                        <div class="card-body">
                            <?php if (count($dados_semanais) > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Data</th>
                                                <th>Dia</th>
                                                <th>Agendamentos</th>
                                                <th>Faturamento</th>
                                                <th>Profissionais</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($dados_semanais as $dia): ?>
                                                <tr>
                                                    <td><?php echo date('d/m/Y', strtotime($dia['data_agendamento'])); ?></td>
                                                    <td><?php echo htmlspecialchars($dia['dia_semana']); ?></td>
                                                    <td><?php echo $dia['total_agendamentos']; ?></td>
                                                    <td>R$ <?php echo number_format($dia['faturamento_total'], 2, ',', '.'); ?></td>
                                                    <td><?php echo htmlspecialchars($dia['profissionais']); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-info">Nenhum agendamento encontrado no período selecionado.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5>Serviços Mais Agendados</h5>
                        </div>
                        <div class="card-body">
                            <?php if (count($servicos_mais_agendados) > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Serviço</th>
                                                <th>Quantidade</th>
                                                <th>Faturamento</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($servicos_mais_agendados as $servico): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($servico['servico']); ?></td>
                                                    <td><?php echo $servico['total']; ?></td>
                                                    <td>R$ <?php echo number_format($servico['faturamento'], 2, ',', '.'); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-info">Nenhum serviço agendado no período.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-header">
                            <h5>Profissionais Mais Ocupados</h5>
                        </div>
                        <div class="card-body">
                            <?php if (count($profissionais_mais_ocupados) > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Profissional</th>
                                                <th>Atendimentos</th>
                                                <th>Faturamento</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($profissionais_mais_ocupados as $profissional): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($profissional['profissional']); ?></td>
                                                    <td><?php echo $profissional['total_atendimentos']; ?></td>
                                                    <td>R$ <?php echo number_format($profissional['faturamento'], 2, ',', '.'); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-info">Nenhum atendimento registrado no período.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mt-4">
                <button class="btn btn-success" onclick="window.print()">
                    <i class="fas fa-print"></i> Imprimir Relatório
                </button>
                <button class="btn btn-info" id="exportExcel">
                    <i class="fas fa-file-excel"></i> Exportar para Excel
                </button>
            </div>
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
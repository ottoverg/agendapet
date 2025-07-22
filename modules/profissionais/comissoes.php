<?php
// Arquivo: /agendapet/modules/profissionais/comissoes.php

require_once '../../includes/config.php';
requireAdmin();

$page_title = 'Comissões de Profissionais';
$custom_js = 'profissionais.js';
include '../../includes/header.php';

// Obter ID do profissional se fornecido
$profissional_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Processar formulário de configuração de comissão
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_comissao'])) {
    $profissional_id = filter_input(INPUT_POST, 'profissional_id', FILTER_VALIDATE_INT);
    $servico_id = filter_input(INPUT_POST, 'servico_id', FILTER_VALIDATE_INT);
    $percentual = filter_input(INPUT_POST, 'percentual', FILTER_VALIDATE_FLOAT);
    
    if ($profissional_id && $servico_id && $percentual !== false) {
        // Verificar se já existe configuração para este profissional/serviço
        $sql_check = "SELECT id FROM profissionais_comissoes 
                     WHERE profissional_id = ? AND servico_id = ?";
        $exists = fetchOne($sql_check, [$profissional_id, $servico_id]);
        
        if ($exists) {
            // Atualizar
            $sql = "UPDATE profissionais_comissoes 
                    SET percentual = ? 
                    WHERE id = ?";
            executeQuery($sql, [$percentual, $exists['id']]);
        } else {
            // Inserir
            $sql = "INSERT INTO profissionais_comissoes 
                    (profissional_id, servico_id, percentual) 
                    VALUES (?, ?, ?)";
            executeQuery($sql, [$profissional_id, $servico_id, $percentual]);
        }
        
        $_SESSION['success_message'] = 'Configuração de comissão salva com sucesso!';
        header('Location: comissoes.php?id=' . $profissional_id);
        exit();
    } else {
        $_SESSION['error_message'] = 'Por favor, preencha todos os campos corretamente.';
    }
}

// Obter profissionais
$profissionais = fetchAll("SELECT id, nome FROM profissionais ORDER BY nome");

// Obter serviços
$servicos = fetchAll("SELECT id, nome FROM servicos ORDER BY nome");

// Obter configurações de comissão se profissional selecionado
$comissoes = [];
if ($profissional_id) {
    $sql = "SELECT pc.servico_id, s.nome as servico_nome, pc.percentual 
            FROM profissionais_comissoes pc
            JOIN servicos s ON pc.servico_id = s.id
            WHERE pc.profissional_id = ?";
    $comissoes = fetchAll($sql, [$profissional_id]);
}

// Obter relatório de comissões se período fornecido
$relatorio = [];
$total_comissao = 0;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate_report'])) {
    $data_inicio = filter_input(INPUT_POST, 'data_inicio', FILTER_SANITIZE_STRING);
    $data_fim = filter_input(INPUT_POST, 'data_fim', FILTER_SANITIZE_STRING);
    
    if ($data_inicio && $data_fim) {
        $sql = "SELECT a.id, a.data_agendamento, a.hora, s.nome as servico, s.preco,
                       p.nome as profissional, pc.percentual,
                       (s.preco * pc.percentual / 100) as comissao
                FROM agendamentos a
                JOIN servicos s ON a.servico_id = s.id
                JOIN profissionais p ON a.profissional_id = p.id
                JOIN profissionais_comissoes pc ON p.id = pc.profissional_id AND s.id = pc.servico_id
                WHERE a.data_agendamento BETWEEN ? AND ?
                AND a.profissional_id = ?
                AND a.status = 'concluido'
                ORDER BY a.data_agendamento, a.hora";
        
        $relatorio = fetchAll($sql, [$data_inicio, $data_fim, $profissional_id]);
        
        // Calcular total da comissão
        $total_comissao = array_sum(array_column($relatorio, 'comissao'));
    } else {
        $_SESSION['error_message'] = 'Por favor, informe o período para gerar o relatório.';
    }
}
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <h2><i class="fas fa-money-bill-wave"></i> Comissões de Profissionais</h2>
            <hr>
            
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Configurar Comissões</h5>
                </div>
                <div class="card-body">
                    <form method="GET" class="form-inline mb-4">
                        <div class="form-group mr-3">
                            <label for="profissional_id" class="mr-2">Profissional:</label>
                            <select class="form-control" id="profissional_id" name="id" required>
                                <option value="">Selecione um profissional</option>
                                <?php foreach ($profissionais as $prof): ?>
                                    <option value="<?php echo $prof['id']; ?>" <?php echo $prof['id'] == $profissional_id ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($prof['nome']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Carregar</button>
                    </form>
                    
                    <?php if ($profissional_id): ?>
                        <h5>Comissões para <?php echo htmlspecialchars($profissionais[array_search($profissional_id, array_column($profissionais, 'id'))]['nome']); ?></h5>
                        
                        <form method="POST">
                            <input type="hidden" name="profissional_id" value="<?php echo $profissional_id; ?>">
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="servico_id">Serviço</label>
                                        <select class="form-control" id="servico_id" name="servico_id" required>
                                            <option value="">Selecione um serviço</option>
                                            <?php foreach ($servicos as $servico): ?>
                                                <option value="<?php echo $servico['id']; ?>">
                                                    <?php echo htmlspecialchars($servico['nome']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="percentual">Percentual (%)</label>
                                        <input type="number" class="form-control" id="percentual" name="percentual" min="0" max="100" step="0.01" required>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>&nbsp;</label>
                                        <button type="submit" name="save_comissao" class="btn btn-success btn-block">Salvar</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                        
                        <?php if (count($comissoes) > 0): ?>
                            <div class="table-responsive mt-4">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Serviço</th>
                                            <th>Percentual</th>
                                            <th>Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($comissoes as $comissao): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($comissao['servico_nome']); ?></td>
                                                <td><?php echo number_format($comissao['percentual'], 2, ',', '.'); ?>%</td>
                                                <td>
                                                    <button class="btn btn-sm btn-primary edit-comissao" 
                                                            data-servico="<?php echo $comissao['servico_id']; ?>"
                                                            data-percentual="<?php echo $comissao['percentual']; ?>">
                                                        <i class="fas fa-edit"></i> Editar
                                                    </button>
                                                    <a href="delete_comissao.php?profissional_id=<?php echo $profissional_id; ?>&servico_id=<?php echo $comissao['servico_id']; ?>" 
                                                       class="btn btn-sm btn-danger" 
                                                       onclick="return confirm('Tem certeza que deseja remover esta configuração de comissão?');">
                                                        <i class="fas fa-trash"></i> Remover
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info mt-4">Nenhuma configuração de comissão encontrada para este profissional.</div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if ($profissional_id): ?>
                <div class="card">
                    <div class="card-header">
                        <h5>Relatório de Comissões</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="profissional_id" value="<?php echo $profissional_id; ?>">
                            
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="data_inicio">Data Início</label>
                                        <input type="date" class="form-control" id="data_inicio" name="data_inicio" value="<?php echo isset($_POST['data_inicio']) ? htmlspecialchars($_POST['data_inicio']) : date('Y-m-01'); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="data_fim">Data Fim</label>
                                        <input type="date" class="form-control" id="data_fim" name="data_fim" value="<?php echo isset($_POST['data_fim']) ? htmlspecialchars($_POST['data_fim']) : date('Y-m-t'); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>&nbsp;</label>
                                        <button type="submit" name="generate_report" class="btn btn-primary btn-block">Gerar Relatório</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                        
                        <?php if (count($relatorio) > 0): ?>
                            <div class="table-responsive mt-4">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Data</th>
                                            <th>Hora</th>
                                            <th>Serviço</th>
                                            <th>Valor</th>
                                            <th>Comissão</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($relatorio as $item): ?>
                                            <tr>
                                                <td><?php echo date('d/m/Y', strtotime($item['data_agendamento'])); ?></td>
                                                <td><?php echo substr($item['hora'], 0, 5); ?></td>
                                                <td><?php echo htmlspecialchars($item['servico']); ?></td>
                                                <td>R$ <?php echo number_format($item['preco'], 2, ',', '.'); ?></td>
                                                <td>R$ <?php echo number_format($item['comissao'], 2, ',', '.'); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr class="font-weight-bold">
                                            <td colspan="4" class="text-right">Total:</td>
                                            <td>R$ <?php echo number_format($total_comissao, 2, ',', '.'); ?></td>
                                        </tr>
                                    </tfoot>
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
                        <?php elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate_report'])): ?>
                            <div class="alert alert-info mt-4">Nenhum agendamento concluído encontrado no período selecionado.</div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Editar comissão ao clicar no botão
    $('.edit-comissao').click(function() {
        const servicoId = $(this).data('servico');
        const percentual = $(this).data('percentual');
        
        $('#servico_id').val(servicoId);
        $('#percentual').val(percentual);
        
        $('html, body').animate({
            scrollTop: $('#servico_id').offset().top - 100
        }, 500);
    });
    
    // Exportar para Excel
    $('#exportExcel').click(function() {
        // Implementar lógica de exportação para Excel
        alert('Exportar para Excel será implementado aqui.');
    });
});
</script>

<?php include '../../includes/footer.php'; ?>
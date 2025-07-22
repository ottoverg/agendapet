<?php
require_once '../../includes/config.php';
requireAuth();

$page_title = 'Lista de Agendamentos';
$custom_js = 'agendamentos.js';
include '../../includes/header.php';

// Filtros
$data = isset($_GET['data']) ? $_GET['data'] : date('Y-m-d');
$data_inicio = isset($_GET['data_inicio']) ? $_GET['data_inicio'] : '';
$data_fim = isset($_GET['data_fim']) ? $_GET['data_fim'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';
$profissional_id = isset($_GET['profissional_id']) ? (int)$_GET['profissional_id'] : 0;

// Construir WHERE
$where = ["a.data_agendamento >= CURDATE()"];
$params = [];

if (!empty($data)) {
    $where = ["a.data_agendamento = ?"];
    $params[] = $data;
}

if (!empty($data_inicio) && !empty($data_fim)) {
    $where = ["a.data_agendamento BETWEEN ? AND ?"];
    $params[] = $data_inicio;
    $params[] = $data_fim;
}

if (!empty($status)) {
    $where[] = "a.status = ?";
    $params[] = $status;
}

if ($profissional_id) {
    $where[] = "a.profissional_id = ?";
    $params[] = $profissional_id;
}

$where_clause = "WHERE " . implode(" AND ", $where);

// Obter agendamentos
$sql = "SELECT a.*, 
        c.nome as cliente_nome, p.nome as pet_nome, 
        s.nome as servico_nome, pr.nome as profissional_nome
        FROM agendamentos a
        JOIN clientes c ON a.cliente_id = c.id
        JOIN pets p ON a.pet_id = p.id
        JOIN servicos s ON a.servico_id = s.id
        JOIN profissionais pr ON a.profissional_id = pr.id
        $where_clause
        ORDER BY a.data_agendamento, a.hora";
$agendamentos = fetchAll($sql, $params);

// Obter profissionais para filtro
$profissionais = fetchAll("SELECT id, nome FROM profissionais ORDER BY nome");
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <h2><i class="far fa-calendar-alt"></i> Lista de Agendamentos</h2>
            <hr>
            
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="form-inline">
                        <div class="form-group mr-2 mb-2">
                            <input type="date" class="form-control" name="data" value="<?php echo htmlspecialchars($data); ?>">
                        </div>
                        <div class="form-group mr-2 mb-2">
                            <input type="date" class="form-control" name="data_inicio" placeholder="Data início" value="<?php echo htmlspecialchars($data_inicio); ?>">
                        </div>
                        <div class="form-group mr-2 mb-2">
                            <input type="date" class="form-control" name="data_fim" placeholder="Data fim" value="<?php echo htmlspecialchars($data_fim); ?>">
                        </div>
                        <div class="form-group mr-2 mb-2">
                            <select class="form-control" name="status">
                                <option value="">Todos status</option>
                                <option value="agendado" <?php echo $status == 'agendado' ? 'selected' : ''; ?>>Agendado</option>
                                <option value="concluido" <?php echo $status == 'concluido' ? 'selected' : ''; ?>>Concluído</option>
                                <option value="cancelado" <?php echo $status == 'cancelado' ? 'selected' : ''; ?>>Cancelado</option>
                            </select>
                        </div>
                        <div class="form-group mr-2 mb-2">
                            <select class="form-control" name="profissional_id">
                                <option value="">Todos profissionais</option>
                                <?php foreach ($profissionais as $prof): ?>
                                    <option value="<?php echo $prof['id']; ?>" <?php echo $prof['id'] == $profissional_id ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($prof['nome']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary mb-2">
                            <i class="fas fa-filter"></i> Filtrar
                        </button>
                        <a href="listar.php" class="btn btn-secondary mb-2 ml-2">
                            <i class="fas fa-times"></i> Limpar
                        </a>
                    </form>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <span>Total de agendamentos: <?php echo count($agendamentos); ?></span>
                        <a href="novo.php" class="btn btn-success btn-sm">
                            <i class="fas fa-plus"></i> Novo Agendamento
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (count($agendamentos) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Data</th>
                                        <th>Hora</th>
                                        <th>Cliente</th>
                                        <th>Pet</th>
                                        <th>Serviço</th>
                                        <th>Profissional</th>
                                        <th>Status</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($agendamentos as $agendamento): ?>
                                        <tr>
                                            <td><?php echo date('d/m/Y', strtotime($agendamento['data_agendamento'])); ?></td>
                                            <td><?php echo substr($agendamento['hora'], 0, 5); ?></td>
                                            <td><?php echo htmlspecialchars($agendamento['cliente_nome']); ?></td>
                                            <td><?php echo htmlspecialchars($agendamento['pet_nome']); ?></td>
                                            <td><?php echo htmlspecialchars($agendamento['servico_nome']); ?></td>
                                            <td><?php echo htmlspecialchars($agendamento['profissional_nome']); ?></td>
                                            <td>
                                                <span class="badge badge-<?php 
                                                    echo $agendamento['status'] == 'concluido' ? 'success' : 
                                                        ($agendamento['status'] == 'cancelado' ? 'danger' : 'primary'); 
                                                ?>">
                                                    <?php echo ucfirst($agendamento['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="editar.php?id=<?php echo $agendamento['id']; ?>" class="btn btn-sm btn-primary" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="excluir.php?id=<?php echo $agendamento['id']; ?>" class="btn btn-sm btn-danger" title="Excluir" onclick="return confirm('Tem certeza que deseja excluir este agendamento?');">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">Nenhum agendamento encontrado.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
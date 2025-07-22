<?php
require_once '../../includes/config.php';
requireAdmin();

$page_title = 'Lista de Serviços';
include '../../includes/header.php';

// Paginação
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Busca e filtros
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$ativo = isset($_GET['ativo']) ? (int)$_GET['ativo'] : '';

$where = [];
$params = [];

if (!empty($search)) {
    $where[] = "nome LIKE ?";
    $params[] = "%$search%";
}

if ($ativo !== '') {
    $where[] = "ativo = ?";
    $params[] = $ativo;
}

$where_clause = $where ? "WHERE " . implode(" AND ", $where) : "";

// Contar total de registros
$sql_count = "SELECT COUNT(*) as total FROM servicos $where_clause";
$total_result = fetchOne($sql_count, $params);
$total = $total_result['total'];
$total_pages = ceil($total / $per_page);

// Obter serviços
$sql = "SELECT * FROM servicos $where_clause ORDER BY nome LIMIT $per_page OFFSET $offset";
$servicos = fetchAll($sql, $params);
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <h2><i class="fas fa-concierge-bell"></i> Lista de Serviços</h2>
            <hr>
            
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="form-inline">
                        <div class="form-group mr-2 mb-2">
                            <input type="text" class="form-control" name="search" placeholder="Buscar por nome..." value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <div class="form-group mr-2 mb-2">
                            <select class="form-control" name="ativo">
                                <option value="">Todos os status</option>
                                <option value="1" <?php echo $ativo === 1 ? 'selected' : ''; ?>>Ativo</option>
                                <option value="0" <?php echo $ativo === 0 ? 'selected' : ''; ?>>Inativo</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary mb-2">
                            <i class="fas fa-filter"></i> Filtrar
                        </button>
                        <?php if ($search || $ativo !== ''): ?>
                            <a href="listar.php" class="btn btn-secondary mb-2 ml-2">
                                <i class="fas fa-times"></i> Limpar
                            </a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <span>Total de serviços: <?php echo $total; ?></span>
                        <a href="cadastro.php" class="btn btn-success btn-sm">
                            <i class="fas fa-plus"></i> Novo Serviço
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (count($servicos) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Nome</th>
                                        <th>Duração</th>
                                        <th>Preço</th>
                                        <th>Status</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($servicos as $servico): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($servico['nome']); ?></td>
                                            <td><?php echo $servico['duracao']; ?> min</td>
                                            <td>R$ <?php echo number_format($servico['preco'], 2, ',', '.'); ?></td>
                                            <td>
                                                <span class="badge badge-<?php echo $servico['ativo'] ? 'success' : 'danger'; ?>">
                                                    <?php echo $servico['ativo'] ? 'Ativo' : 'Inativo'; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="editar.php?id=<?php echo $servico['id']; ?>" class="btn btn-sm btn-primary" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="excluir.php?id=<?php echo $servico['id']; ?>" class="btn btn-sm btn-danger" title="Excluir" onclick="return confirm('Tem certeza que deseja excluir este serviço?');">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Paginação -->
                        <?php if ($total_pages > 1): ?>
                            <nav aria-label="Page navigation">
                                <ul class="pagination justify-content-center">
                                    <?php if ($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&ativo=<?php echo $ativo; ?>" aria-label="Previous">
                                                <span aria-hidden="true">&laquo;</span>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                    
                                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&ativo=<?php echo $ativo; ?>"><?php echo $i; ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    
                                    <?php if ($page < $total_pages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&ativo=<?php echo $ativo; ?>" aria-label="Next">
                                                <span aria-hidden="true">&raquo;</span>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="alert alert-info">Nenhum serviço encontrado.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
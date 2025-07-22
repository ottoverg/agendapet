<?php
require_once '../../includes/config.php';
requireAdmin();

$page_title = 'Lista de Raças';
include '../../includes/header.php';

// Paginação
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Busca e filtros
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$especie = isset($_GET['especie']) ? $_GET['especie'] : '';

$where = [];
$params = [];

if (!empty($search)) {
    $where[] = "nome LIKE ?";
    $params[] = "%$search%";
}

if ($especie) {
    $where[] = "especie = ?";
    $params[] = $especie;
}

$where_clause = $where ? "WHERE " . implode(" AND ", $where) : "";

// Contar total de registros
$sql_count = "SELECT COUNT(*) as total FROM racas $where_clause";
$total_result = fetchOne($sql_count, $params);
$total = $total_result['total'];
$total_pages = ceil($total / $per_page);

// Obter raças
$sql = "SELECT * FROM racas $where_clause ORDER BY especie, nome LIMIT $per_page OFFSET $offset";
$racas = fetchAll($sql, $params);
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <h2><i class="fas fa-dog"></i> Lista de Raças</h2>
            <hr>
            
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="form-inline">
                        <div class="form-group mr-2 mb-2">
                            <input type="text" class="form-control" name="search" placeholder="Buscar por nome..." value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <div class="form-group mr-2 mb-2">
                            <select class="form-control" name="especie">
                                <option value="">Todas as espécies</option>
                                <option value="Cachorro" <?php echo $especie == 'Cachorro' ? 'selected' : ''; ?>>Cachorro</option>
                                <option value="Gato" <?php echo $especie == 'Gato' ? 'selected' : ''; ?>>Gato</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary mb-2">
                            <i class="fas fa-filter"></i> Filtrar
                        </button>
                        <?php if ($search || $especie): ?>
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
                        <span>Total de raças: <?php echo $total; ?></span>
                        <a href="cadastro.php" class="btn btn-success btn-sm">
                            <i class="fas fa-plus"></i> Nova Raça
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (count($racas) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Espécie</th>
                                        <th>Nome</th>
                                        <th>Cadastro</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($racas as $raca): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($raca['especie']); ?></td>
                                            <td><?php echo htmlspecialchars($raca['nome']); ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($raca['data_cadastro'])); ?></td>
                                            <td>
                                                <a href="editar.php?id=<?php echo $raca['id']; ?>" class="btn btn-sm btn-primary" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="excluir.php?id=<?php echo $raca['id']; ?>" class="btn btn-sm btn-danger" title="Excluir" onclick="return confirm('Tem certeza que deseja excluir esta raça?');">
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
                                            <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&especie=<?php echo $especie; ?>" aria-label="Previous">
                                                <span aria-hidden="true">&laquo;</span>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                    
                                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&especie=<?php echo $especie; ?>"><?php echo $i; ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    
                                    <?php if ($page < $total_pages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&especie=<?php echo $especie; ?>" aria-label="Next">
                                                <span aria-hidden="true">&raquo;</span>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="alert alert-info">Nenhuma raça encontrada.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
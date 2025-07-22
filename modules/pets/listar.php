<?php
require_once '../../includes/config.php';
requireAuth();

$page_title = 'Lista de Pets';
$custom_js = 'pets.js';
include '../../includes/header.php';

// Paginação
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Busca e filtros
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$cliente_id = isset($_GET['cliente_id']) ? (int)$_GET['cliente_id'] : 0;
$especie = isset($_GET['especie']) ? $_GET['especie'] : '';

$where = [];
$params = [];

if (!empty($search)) {
    $where[] = "(p.nome LIKE ? OR c.nome LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($cliente_id) {
    $where[] = "p.cliente_id = ?";
    $params[] = $cliente_id;
}

if ($especie) {
    $where[] = "p.especie = ?";
    $params[] = $especie;
}

$where_clause = $where ? "WHERE " . implode(" AND ", $where) : "";

// Contar total de registros
$sql_count = "SELECT COUNT(*) as total 
              FROM pets p
              LEFT JOIN clientes c ON p.cliente_id = c.id
              $where_clause";
$total_result = fetchOne($sql_count, $params);
$total = $total_result['total'];
$total_pages = ceil($total / $per_page);

// Obter pets
$sql = "SELECT p.*, c.nome as cliente_nome, r.nome as raca_nome 
        FROM pets p
        LEFT JOIN clientes c ON p.cliente_id = c.id
        LEFT JOIN racas r ON p.raca_id = r.id
        $where_clause
        ORDER BY p.nome 
        LIMIT $per_page OFFSET $offset";
$pets = fetchAll($sql, $params);

// Obter clientes para filtro
$clientes = fetchAll("SELECT id, nome FROM clientes ORDER BY nome");
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <h2><i class="fas fa-paw"></i> Lista de Pets</h2>
            <hr>
            
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="form-inline">
                        <div class="form-group mr-2 mb-2">
                            <input type="text" class="form-control" name="search" placeholder="Buscar por nome..." value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <div class="form-group mr-2 mb-2">
                            <select class="form-control" name="cliente_id">
                                <option value="">Todos os donos</option>
                                <?php foreach ($clientes as $cliente): ?>
                                    <option value="<?php echo $cliente['id']; ?>" <?php echo $cliente['id'] == $cliente_id ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cliente['nome']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
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
                        <?php if ($search || $cliente_id || $especie): ?>
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
                        <span>Total de pets: <?php echo $total; ?></span>
                        <a href="cadastro.php" class="btn btn-success btn-sm">
                            <i class="fas fa-plus"></i> Novo Pet
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (count($pets) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Nome</th>
                                        <th>Dono</th>
                                        <th>Espécie</th>
                                        <th>Raça</th>
                                        <th>Gênero</th>
                                        <th>Tamanho</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pets as $pet): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($pet['nome']); ?></td>
                                            <td><?php echo htmlspecialchars($pet['cliente_nome']); ?></td>
                                            <td><?php echo htmlspecialchars($pet['especie']); ?></td>
                                            <td><?php echo htmlspecialchars($pet['raca_nome']); ?></td>
                                            <td><?php echo htmlspecialchars($pet['genero']); ?></td>
                                            <td><?php echo htmlspecialchars($pet['tamanho']); ?></td>
                                            <td>
                                                <a href="editar.php?id=<?php echo $pet['id']; ?>" class="btn btn-sm btn-primary" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="excluir.php?id=<?php echo $pet['id']; ?>" class="btn btn-sm btn-danger" title="Excluir" onclick="return confirm('Tem certeza que deseja excluir este pet?');">
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
                                            <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&cliente_id=<?php echo $cliente_id; ?>&especie=<?php echo $especie; ?>" aria-label="Previous">
                                                <span aria-hidden="true">&laquo;</span>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                    
                                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&cliente_id=<?php echo $cliente_id; ?>&especie=<?php echo $especie; ?>"><?php echo $i; ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    
                                    <?php if ($page < $total_pages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&cliente_id=<?php echo $cliente_id; ?>&especie=<?php echo $especie; ?>" aria-label="Next">
                                                <span aria-hidden="true">&raquo;</span>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="alert alert-info">Nenhum pet encontrado.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
<?php
require_once '../../includes/config.php';
requireAdmin();

$page_title = 'Lista de Usuários';
include '../../includes/header.php';

// Paginação
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Busca e filtros
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$role = isset($_GET['role']) ? $_GET['role'] : '';
$ativo = isset($_GET['ativo']) ? (int)$_GET['ativo'] : '';

$where = [];
$params = [];

if (!empty($search)) {
    $where[] = "(nome LIKE ? OR email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($role) {
    $where[] = "role = ?";
    $params[] = $role;
}

if ($ativo !== '') {
    $where[] = "ativo = ?";
    $params[] = $ativo;
}

$where_clause = $where ? "WHERE " . implode(" AND ", $where) : "";

// Contar total de registros
$sql_count = "SELECT COUNT(*) as total FROM usuarios $where_clause";
$total_result = fetchOne($sql_count, $params);
$total = $total_result['total'];
$total_pages = ceil($total / $per_page);

// Obter usuários
$sql = "SELECT * FROM usuarios $where_clause ORDER BY nome LIMIT $per_page OFFSET $offset";
$usuarios = fetchAll($sql, $params);
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <h2><i class="fas fa-users"></i> Lista de Usuários</h2>
            <hr>
            
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="form-inline">
                        <div class="form-group mr-2 mb-2">
                            <input type="text" class="form-control" name="search" placeholder="Buscar por nome ou e-mail..." value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <div class="form-group mr-2 mb-2">
                            <select class="form-control" name="role">
                                <option value="">Todos os tipos</option>
                                <option value="admin" <?php echo $role == 'admin' ? 'selected' : ''; ?>>Administrador</option>
                                <option value="user" <?php echo $role == 'user' ? 'selected' : ''; ?>>Usuário</option>
                            </select>
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
                        <?php if ($search || $role || $ativo !== ''): ?>
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
                        <span>Total de usuários: <?php echo $total; ?></span>
                        <a href="cadastro.php" class="btn btn-success btn-sm">
                            <i class="fas fa-plus"></i> Novo Usuário
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (count($usuarios) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Nome</th>
                                        <th>E-mail</th>
                                        <th>Tipo</th>
                                        <th>Status</th>
                                        <th>Cadastro</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($usuarios as $usuario): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($usuario['nome']); ?></td>
                                            <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                                            <td><?php echo $usuario['role'] == 'admin' ? 'Administrador' : 'Usuário'; ?></td>
                                            <td>
                                                <span class="badge badge-<?php echo $usuario['ativo'] ? 'success' : 'danger'; ?>">
                                                    <?php echo $usuario['ativo'] ? 'Ativo' : 'Inativo'; ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('d/m/Y', strtotime($usuario['data_cadastro'])); ?></td>
                                            <td>
                                                <a href="editar.php?id=<?php echo $usuario['id']; ?>" class="btn btn-sm btn-primary" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <?php if ($usuario['id'] != $_SESSION['user_id']): ?>
                                                    <a href="excluir.php?id=<?php echo $usuario['id']; ?>" class="btn btn-sm btn-danger" title="Excluir" onclick="return confirm('Tem certeza que deseja excluir este usuário?');">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                <?php endif; ?>
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
                                            <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&role=<?php echo $role; ?>&ativo=<?php echo $ativo; ?>" aria-label="Previous">
                                                <span aria-hidden="true">&laquo;</span>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                    
                                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&role=<?php echo $role; ?>&ativo=<?php echo $ativo; ?>"><?php echo $i; ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    
                                    <?php if ($page < $total_pages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&role=<?php echo $role; ?>&ativo=<?php echo $ativo; ?>" aria-label="Next">
                                                <span aria-hidden="true">&raquo;</span>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="alert alert-info">Nenhum usuário encontrado.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
<?php
require_once '../../includes/config.php';
requireAdmin();

$page_title = 'Editar Usuário';
include '../../includes/header.php';

$usuario_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$usuario_id) {
    header('Location: listar.php');
    exit();
}

// Obter dados do usuário
$usuario = fetchOne("SELECT * FROM usuarios WHERE id = ?", [$usuario_id]);

if (!$usuario) {
    $_SESSION['error_message'] = 'Usuário não encontrado.';
    header('Location: listar.php');
    exit();
}

// Processar formulário se for submetido
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $senha = $_POST['senha'];
    $confirmar_senha = $_POST['confirmar_senha'];
    $role = filter_input(INPUT_POST, 'role', FILTER_SANITIZE_STRING);
    $ativo = isset($_POST['ativo']) ? 1 : 0;
    
    // Validar dados
    if (!$nome || !$email || !$role) {
        $_SESSION['error_message'] = 'Por favor, preencha todos os campos obrigatórios.';
    } elseif ($senha && $senha !== $confirmar_senha) {
        $_SESSION['error_message'] = 'As senhas não coincidem.';
    } elseif ($senha && strlen($senha) < 6) {
        $_SESSION['error_message'] = 'A senha deve ter pelo menos 6 caracteres.';
    } else {
        // Verificar se e-mail já existe em outro usuário
        $sql = "SELECT id FROM usuarios WHERE email = ? AND id != ?";
        $exists = fetchOne($sql, [$email, $usuario_id]);
        
        if ($exists) {
            $_SESSION['error_message'] = 'Já existe outro usuário cadastrado com este e-mail.';
        } else {
            // Preparar query para atualização
            if ($senha) {
                $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
                $sql = "UPDATE usuarios SET 
                        nome = ?, email = ?, senha = ?, role = ?, ativo = ?
                        WHERE id = ?";
                $params = [$nome, $email, $senha_hash, $role, $ativo, $usuario_id];
            } else {
                $sql = "UPDATE usuarios SET 
                        nome = ?, email = ?, role = ?, ativo = ?
                        WHERE id = ?";
                $params = [$nome, $email, $role, $ativo, $usuario_id];
            }
            
            try {
                executeQuery($sql, $params);
                $_SESSION['success_message'] = 'Usuário atualizado com sucesso!';
                header('Location: listar.php');
                exit();
            } catch (PDOException $e) {
                $_SESSION['error_message'] = 'Erro ao atualizar usuário: ' . $e->getMessage();
            }
        }
    }
}
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <h2><i class="fas fa-user-edit"></i> Editar Usuário</h2>
            <hr>
            
            <form method="POST" class="needs-validation" novalidate>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="nome">Nome Completo *</label>
                            <input type="text" class="form-control" id="nome" name="nome" value="<?php echo isset($_POST['nome']) ? htmlspecialchars($_POST['nome']) : htmlspecialchars($usuario['nome']); ?>" required>
                            <div class="invalid-feedback">Por favor, informe o nome completo.</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="email">E-mail *</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : htmlspecialchars($usuario['email']); ?>" required>
                            <div class="invalid-feedback">Por favor, informe um e-mail válido.</div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="senha">Nova Senha</label>
                            <input type="password" class="form-control" id="senha" name="senha">
                            <small class="form-text text-muted">Deixe em branco para manter a senha atual.</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="confirmar_senha">Confirmar Nova Senha</label>
                            <input type="password" class="form-control" id="confirmar_senha" name="confirmar_senha">
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="role">Tipo de Usuário *</label>
                            <select class="form-control" id="role" name="role" required>
                                <option value="">Selecione</option>
                                <option value="admin" <?php echo (isset($_POST['role']) ? $_POST['role'] : $usuario['role']) == 'admin' ? 'selected' : ''; ?>>Administrador</option>
                                <option value="user" <?php echo (isset($_POST['role']) ? $_POST['role'] : $usuario['role']) == 'user' ? 'selected' : ''; ?>>Usuário</option>
                            </select>
                            <div class="invalid-feedback">Por favor, selecione o tipo de usuário.</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="ativo" name="ativo" value="1" <?php echo (isset($_POST['ativo']) ? $_POST['ativo'] : $usuario['ativo']) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="ativo">Ativo</label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Salvar Alterações</button>
                <a href="listar.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Voltar</a>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
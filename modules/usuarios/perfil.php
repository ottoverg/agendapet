<?php
require_once '../../includes/config.php';
requireAuth();

$page_title = 'Meu Perfil';
include '../../includes/header.php';

$usuario_id = $_SESSION['user_id'];

// Obter dados do usuário
$usuario = fetchOne("SELECT * FROM usuarios WHERE id = ?", [$usuario_id]);

if (!$usuario) {
    $_SESSION['error_message'] = 'Usuário não encontrado.';
    header('Location: ../../index.php');
    exit();
}

// Processar formulário se for submetido
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $senha_atual = $_POST['senha_atual'] ?? '';
    $nova_senha = $_POST['nova_senha'] ?? '';
    $confirmar_senha = $_POST['confirmar_senha'] ?? '';
    
    // Validar dados básicos
    if (!$nome || !$email) {
        $_SESSION['error_message'] = 'Por favor, preencha todos os campos obrigatórios.';
    } else {
        // Verificar se e-mail já existe em outro usuário
        $sql = "SELECT id FROM usuarios WHERE email = ? AND id != ?";
        $exists = fetchOne($sql, [$email, $usuario_id]);
        
        if ($exists) {
            $_SESSION['error_message'] = 'Já existe outro usuário cadastrado com este e-mail.';
        } else {
            // Atualizar dados básicos
            $sql = "UPDATE usuarios SET nome = ?, email = ? WHERE id = ?";
            $params = [$nome, $email, $usuario_id];
            
            // Verificar se quer alterar a senha
            if ($nova_senha) {
                if (!password_verify($senha_atual, $usuario['senha'])) {
                    $_SESSION['error_message'] = 'Senha atual incorreta.';
                } elseif ($nova_senha !== $confirmar_senha) {
                    $_SESSION['error_message'] = 'As novas senhas não coincidem.';
                } elseif (strlen($nova_senha) < 6) {
                    $_SESSION['error_message'] = 'A senha deve ter pelo menos 6 caracteres.';
                } else {
                    $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
                    $sql = "UPDATE usuarios SET nome = ?, email = ?, senha = ? WHERE id = ?";
                    $params = [$nome, $email, $senha_hash, $usuario_id];
                }
            }
            
            if (!isset($_SESSION['error_message'])) {
                try {
                    executeQuery($sql, $params);
                    $_SESSION['success_message'] = 'Perfil atualizado com sucesso!';
                    
                    // Atualizar dados na sessão
                    $_SESSION['user_name'] = $nome;
                    $_SESSION['user_email'] = $email;
                    
                    header('Location: perfil.php');
                    exit();
                } catch (PDOException $e) {
                    $_SESSION['error_message'] = 'Erro ao atualizar perfil: ' . $e->getMessage();
                }
            }
        }
    }
}
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-user"></i> Meu Perfil</h3>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['error_message'])): ?>
                        <div class="alert alert-danger">
                            <?php echo $_SESSION['error_message']; ?>
                            <?php unset($_SESSION['error_message']); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['success_message'])): ?>
                        <div class="alert alert-success">
                            <?php echo $_SESSION['success_message']; ?>
                            <?php unset($_SESSION['success_message']); ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="form-group">
                            <label for="nome">Nome Completo *</label>
                            <input type="text" class="form-control" id="nome" name="nome" 
                                   value="<?php echo htmlspecialchars($usuario['nome']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">E-mail *</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($usuario['email']); ?>" required>
                        </div>
                        
                        <hr>
                        
                        <h5>Alterar Senha</h5>
                        <div class="form-group">
                            <label for="senha_atual">Senha Atual</label>
                            <input type="password" class="form-control" id="senha_atual" name="senha_atual">
                            <small class="form-text text-muted">Preencha apenas se desejar alterar a senha</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="nova_senha">Nova Senha</label>
                            <input type="password" class="form-control" id="nova_senha" name="nova_senha">
                        </div>
                        
                        <div class="form-group">
                            <label for="confirmar_senha">Confirmar Nova Senha</label>
                            <input type="password" class="form-control" id="confirmar_senha" name="confirmar_senha">
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Salvar Alterações
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
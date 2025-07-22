<?php
require_once '../../includes/config.php';
requireAdmin();

$page_title = 'Cadastrar Usuário';
include '../../includes/header.php';

// Processar formulário se for submetido
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $senha = $_POST['senha'];
    $confirmar_senha = $_POST['confirmar_senha'];
    $role = filter_input(INPUT_POST, 'role', FILTER_SANITIZE_STRING);
    
    // Validar dados
    if (!$nome || !$email || !$senha || !$confirmar_senha || !$role) {
        $_SESSION['error_message'] = 'Por favor, preencha todos os campos.';
    } elseif ($senha !== $confirmar_senha) {
        $_SESSION['error_message'] = 'As senhas não coincidem.';
    } elseif (strlen($senha) < 6) {
        $_SESSION['error_message'] = 'A senha deve ter pelo menos 6 caracteres.';
    } else {
        // Verificar se e-mail já existe
        $sql = "SELECT id FROM usuarios WHERE email = ?";
        $exists = fetchOne($sql, [$email]);
        
        if ($exists) {
            $_SESSION['error_message'] = 'Já existe um usuário cadastrado com este e-mail.';
        } else {
            // Criptografar senha
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
            
            // Inserir no banco de dados
            $sql = "INSERT INTO usuarios (nome, email, senha, role, data_cadastro) 
                    VALUES (?, ?, ?, ?, NOW())";
            
            try {
                executeQuery($sql, [$nome, $email, $senha_hash, $role]);
                $_SESSION['success_message'] = 'Usuário cadastrado com sucesso!';
                header('Location: listar.php');
                exit();
            } catch (PDOException $e) {
                $_SESSION['error_message'] = 'Erro ao cadastrar usuário: ' . $e->getMessage();
            }
        }
    }
}
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <h2><i class="fas fa-user-plus"></i> Cadastrar Novo Usuário</h2>
            <hr>
            
            <form method="POST" class="needs-validation" novalidate>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="nome">Nome Completo *</label>
                            <input type="text" class="form-control" id="nome" name="nome" value="<?php echo isset($_POST['nome']) ? htmlspecialchars($_POST['nome']) : ''; ?>" required>
                            <div class="invalid-feedback">Por favor, informe o nome completo.</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="email">E-mail *</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                            <div class="invalid-feedback">Por favor, informe um e-mail válido.</div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="senha">Senha *</label>
                            <input type="password" class="form-control" id="senha" name="senha" required>
                            <div class="invalid-feedback">Por favor, informe a senha.</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="confirmar_senha">Confirmar Senha *</label>
                            <input type="password" class="form-control" id="confirmar_senha" name="confirmar_senha" required>
                            <div class="invalid-feedback">Por favor, confirme a senha.</div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="role">Tipo de Usuário *</label>
                            <select class="form-control" id="role" name="role" required>
                                <option value="">Selecione</option>
                                <option value="admin" <?php echo isset($_POST['role']) && $_POST['role'] == 'admin' ? 'selected' : ''; ?>>Administrador</option>
                                <option value="user" <?php echo isset($_POST['role']) && $_POST['role'] == 'user' ? 'selected' : ''; ?>>Usuário</option>
                            </select>
                            <div class="invalid-feedback">Por favor, selecione o tipo de usuário.</div>
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Salvar</button>
                <a href="listar.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Voltar</a>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
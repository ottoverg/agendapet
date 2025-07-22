<?php
require_once '../../includes/config.php';
requireAdmin();

$page_title = 'Cadastrar Serviço';
include '../../includes/header.php';

// Processar formulário se for submetido
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_STRING);
    $descricao = filter_input(INPUT_POST, 'descricao', FILTER_SANITIZE_STRING);
    $duracao = filter_input(INPUT_POST, 'duracao', FILTER_VALIDATE_INT);
    $preco = filter_input(INPUT_POST, 'preco', FILTER_SANITIZE_STRING);
    
    // Validar dados
    if (!$nome || !$duracao || !$preco) {
        $_SESSION['error_message'] = 'Por favor, preencha todos os campos obrigatórios.';
    } elseif ($duracao <= 0) {
        $_SESSION['error_message'] = 'A duração deve ser maior que zero.';
    } else {
        // Converter preço para formato decimal
        $preco = str_replace(',', '.', str_replace('.', '', $preco));
        $preco = (float)$preco;
        
        if ($preco <= 0) {
            $_SESSION['error_message'] = 'O preço deve ser maior que zero.';
        } else {
            // Verificar se serviço já existe
            $sql = "SELECT id FROM servicos WHERE nome = ?";
            $exists = fetchOne($sql, [$nome]);
            
            if ($exists) {
                $_SESSION['error_message'] = 'Já existe um serviço cadastrado com este nome.';
            } else {
                // Inserir no banco de dados
                $sql = "INSERT INTO servicos (nome, descricao, duracao, preco, ativo, data_cadastro) 
                        VALUES (?, ?, ?, ?, 1, NOW())";
                
                try {
                    executeQuery($sql, [$nome, $descricao, $duracao, $preco]);
                    $_SESSION['success_message'] = 'Serviço cadastrado com sucesso!';
                    header('Location: listar.php');
                    exit();
                } catch (PDOException $e) {
                    $_SESSION['error_message'] = 'Erro ao cadastrar serviço: ' . $e->getMessage();
                }
            }
        }
    }
}
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <h2><i class="fas fa-concierge-bell"></i> Cadastrar Novo Serviço</h2>
            <hr>
            
            <form method="POST" class="needs-validation" novalidate>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="nome">Nome do Serviço *</label>
                            <input type="text" class="form-control" id="nome" name="nome" value="<?php echo isset($_POST['nome']) ? htmlspecialchars($_POST['nome']) : ''; ?>" required>
                            <div class="invalid-feedback">Por favor, informe o nome do serviço.</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="duracao">Duração (minutos) *</label>
                            <input type="number" class="form-control" id="duracao" name="duracao" min="1" value="<?php echo isset($_POST['duracao']) ? htmlspecialchars($_POST['duracao']) : ''; ?>" required>
                            <div class="invalid-feedback">Por favor, informe a duração em minutos.</div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="preco">Preço (R$) *</label>
                            <input type="text" class="form-control money" id="preco" name="preco" value="<?php echo isset($_POST['preco']) ? htmlspecialchars($_POST['preco']) : ''; ?>" required>
                            <div class="invalid-feedback">Por favor, informe o preço.</div>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="descricao">Descrição</label>
                    <textarea class="form-control" id="descricao" name="descricao" rows="3"><?php echo isset($_POST['descricao']) ? htmlspecialchars($_POST['descricao']) : ''; ?></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Salvar</button>
                <a href="listar.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Voltar</a>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Máscara para o campo de preço
    $('.money').mask('#.##0,00', {reverse: true});
});
</script>

<?php include '../../includes/footer.php'; ?>
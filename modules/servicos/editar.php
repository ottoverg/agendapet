<?php
require_once '../../includes/config.php';
requireAdmin();

$page_title = 'Editar Serviço';
include '../../includes/header.php';

$servico_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$servico_id) {
    header('Location: listar.php');
    exit();
}

// Obter dados do serviço
$servico = fetchOne("SELECT * FROM servicos WHERE id = ?", [$servico_id]);

if (!$servico) {
    $_SESSION['error_message'] = 'Serviço não encontrado.';
    header('Location: listar.php');
    exit();
}

// Processar formulário se for submetido
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_STRING);
    $descricao = filter_input(INPUT_POST, 'descricao', FILTER_SANITIZE_STRING);
    $duracao = filter_input(INPUT_POST, 'duracao', FILTER_VALIDATE_INT);
    $preco = filter_input(INPUT_POST, 'preco', FILTER_SANITIZE_STRING);
    $ativo = isset($_POST['ativo']) ? 1 : 0;
    
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
            // Verificar se serviço já existe (outro ID)
            $sql = "SELECT id FROM servicos WHERE nome = ? AND id != ?";
            $exists = fetchOne($sql, [$nome, $servico_id]);
            
            if ($exists) {
                $_SESSION['error_message'] = 'Já existe outro serviço cadastrado com este nome.';
            } else {
                // Atualizar no banco de dados
                $sql = "UPDATE servicos SET 
                        nome = ?, descricao = ?, duracao = ?, preco = ?, ativo = ?
                        WHERE id = ?";
                
                try {
                    executeQuery($sql, [$nome, $descricao, $duracao, $preco, $ativo, $servico_id]);
                    $_SESSION['success_message'] = 'Serviço atualizado com sucesso!';
                    header('Location: listar.php');
                    exit();
                } catch (PDOException $e) {
                    $_SESSION['error_message'] = 'Erro ao atualizar serviço: ' . $e->getMessage();
                }
            }
        }
    }
}
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <h2><i class="fas fa-concierge-bell"></i> Editar Serviço</h2>
            <hr>
            
            <form method="POST" class="needs-validation" novalidate>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="nome">Nome do Serviço *</label>
                            <input type="text" class="form-control" id="nome" name="nome" value="<?php echo isset($_POST['nome']) ? htmlspecialchars($_POST['nome']) : htmlspecialchars($servico['nome']); ?>" required>
                            <div class="invalid-feedback">Por favor, informe o nome do serviço.</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="duracao">Duração (minutos) *</label>
                            <input type="number" class="form-control" id="duracao" name="duracao" min="1" value="<?php echo isset($_POST['duracao']) ? htmlspecialchars($_POST['duracao']) : htmlspecialchars($servico['duracao']); ?>" required>
                            <div class="invalid-feedback">Por favor, informe a duração em minutos.</div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="preco">Preço (R$) *</label>
                            <input type="text" class="form-control money" id="preco" name="preco" value="<?php echo isset($_POST['preco']) ? htmlspecialchars($_POST['preco']) : number_format($servico['preco'], 2, ',', '.'); ?>" required>
                            <div class="invalid-feedback">Por favor, informe o preço.</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="ativo" name="ativo" value="1" <?php echo (isset($_POST['ativo']) ? $_POST['ativo'] : $servico['ativo']) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="ativo">Ativo</label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="descricao">Descrição</label>
                    <textarea class="form-control" id="descricao" name="descricao" rows="3"><?php echo isset($_POST['descricao']) ? htmlspecialchars($_POST['descricao']) : htmlspecialchars($servico['descricao']); ?></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Salvar Alterações</button>
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
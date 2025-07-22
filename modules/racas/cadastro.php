<?php
require_once '../../includes/config.php';
requireAdmin();

$page_title = 'Cadastrar Raça';
include '../../includes/header.php';

// Processar formulário se for submetido
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $especie = filter_input(INPUT_POST, 'especie', FILTER_SANITIZE_STRING);
    $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_STRING);
    
    // Validar dados
    if (!$especie || !$nome) {
        $_SESSION['error_message'] = 'Por favor, preencha todos os campos.';
    } else {
        // Verificar se raça já existe para esta espécie
        $sql = "SELECT id FROM racas WHERE especie = ? AND nome = ?";
        $exists = fetchOne($sql, [$especie, $nome]);
        
        if ($exists) {
            $_SESSION['error_message'] = 'Já existe uma raça cadastrada com este nome para a espécie selecionada.';
        } else {
            // Inserir no banco de dados
            $sql = "INSERT INTO racas (especie, nome, data_cadastro) VALUES (?, ?, NOW())";
            
            try {
                executeQuery($sql, [$especie, $nome]);
                $_SESSION['success_message'] = 'Raça cadastrada com sucesso!';
                header('Location: listar.php');
                exit();
            } catch (PDOException $e) {
                $_SESSION['error_message'] = 'Erro ao cadastrar raça: ' . $e->getMessage();
            }
        }
    }
}
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <h2><i class="fas fa-dog"></i> Cadastrar Nova Raça</h2>
            <hr>
            
            <form method="POST" class="needs-validation" novalidate>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="especie">Espécie *</label>
                            <select class="form-control" id="especie" name="especie" required>
                                <option value="">Selecione a espécie</option>
                                <option value="Cachorro" <?php echo isset($_POST['especie']) && $_POST['especie'] == 'Cachorro' ? 'selected' : ''; ?>>Cachorro</option>
                                <option value="Gato" <?php echo isset($_POST['especie']) && $_POST['especie'] == 'Gato' ? 'selected' : ''; ?>>Gato</option>
                            </select>
                            <div class="invalid-feedback">Por favor, selecione a espécie.</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="nome">Nome da Raça *</label>
                            <input type="text" class="form-control" id="nome" name="nome" value="<?php echo isset($_POST['nome']) ? htmlspecialchars($_POST['nome']) : ''; ?>" required>
                            <div class="invalid-feedback">Por favor, informe o nome da raça.</div>
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
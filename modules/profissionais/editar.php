<?php
require_once '../../includes/config.php';
requireAdmin();

$page_title = 'Editar Profissional';
$custom_js = 'profissionais.js';
include '../../includes/header.php';

$profissional_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$profissional_id) {
    header('Location: listar.php');
    exit();
}

// Obter dados do profissional
$profissional = fetchOne("SELECT * FROM profissionais WHERE id = ?", [$profissional_id]);

if (!$profissional) {
    $_SESSION['error_message'] = 'Profissional não encontrado.';
    header('Location: listar.php');
    exit();
}

// Processar formulário se for submetido
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_STRING);
    $cpf = filter_input(INPUT_POST, 'cpf', FILTER_SANITIZE_STRING);
    $cep = filter_input(INPUT_POST, 'cep', FILTER_SANITIZE_STRING);
    $logradouro = filter_input(INPUT_POST, 'logradouro', FILTER_SANITIZE_STRING);
    $numero = filter_input(INPUT_POST, 'numero', FILTER_SANITIZE_STRING);
    $complemento = filter_input(INPUT_POST, 'complemento', FILTER_SANITIZE_STRING);
    $bairro = filter_input(INPUT_POST, 'bairro', FILTER_SANITIZE_STRING);
    $cidade = filter_input(INPUT_POST, 'cidade', FILTER_SANITIZE_STRING);
    $uf = filter_input(INPUT_POST, 'uf', FILTER_SANITIZE_STRING);
    $telefone = filter_input(INPUT_POST, 'telefone', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $banhista = isset($_POST['banhista']) ? 1 : 0;
    $tosador = isset($_POST['tosador']) ? 1 : 0;
    $observacoes = filter_input(INPUT_POST, 'observacoes', FILTER_SANITIZE_STRING);
    
    // Validar CPF
    if (!validaCPF($cpf)) {
        $_SESSION['error_message'] = 'CPF inválido. Por favor, verifique o número digitado.';
    } else {
        // Verificar se CPF já existe em outro profissional
        $sql = "SELECT id FROM profissionais WHERE cpf = ? AND id != ?";
        $exists = fetchOne($sql, [$cpf, $profissional_id]);
        
        if ($exists) {
            $_SESSION['error_message'] = 'Já existe outro profissional cadastrado com este CPF.';
        } else {
            // Atualizar no banco de dados
            $sql = "UPDATE profissionais SET 
                    nome = ?, cpf = ?, cep = ?, logradouro = ?, numero = ?, 
                    complemento = ?, bairro = ?, cidade = ?, uf = ?, telefone = ?, 
                    email = ?, banhista = ?, tosador = ?, observacoes = ?
                    WHERE id = ?";
            $params = [$nome, $cpf, $cep, $logradouro, $numero, $complemento, 
                      $bairro, $cidade, $uf, $telefone, $email, $banhista, $tosador, 
                      $observacoes, $profissional_id];
            
            try {
                executeQuery($sql, $params);
                $_SESSION['success_message'] = 'Profissional atualizado com sucesso!';
                header('Location: listar.php');
                exit();
            } catch (PDOException $e) {
                $_SESSION['error_message'] = 'Erro ao atualizar profissional: ' . $e->getMessage();
            }
        }
    }
}

function validaCPF($cpf) {
    $cpf = preg_replace('/[^0-9]/', '', $cpf);
    
    if (strlen($cpf) != 11 || preg_match('/(\d)\1{10}/', $cpf)) {
        return false;
    }
    
    for ($t = 9; $t < 11; $t++) {
        for ($d = 0, $c = 0; $c < $t; $c++) {
            $d += $cpf[$c] * (($t + 1) - $c);
        }
        $d = ((10 * $d) % 11) % 10;
        if ($cpf[$c] != $d) {
            return false;
        }
    }
    return true;
}
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <h2><i class="fas fa-user-md"></i> Editar Profissional</h2>
            <hr>
            
            <form method="POST" class="needs-validation" novalidate>
                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label for="nome">Nome Completo *</label>
                            <input type="text" class="form-control" id="nome" name="nome" value="<?php echo htmlspecialchars($profissional['nome']); ?>" required>
                            <div class="invalid-feedback">Por favor, informe o nome completo.</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="cpf">CPF *</label>
                            <input type="text" class="form-control cpf" id="cpf" name="cpf" value="<?php echo htmlspecialchars($profissional['cpf']); ?>" required>
                            <div class="invalid-feedback">Por favor, informe um CPF válido.</div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="cep">CEP *</label>
                            <input type="text" class="form-control cep" id="cep" name="cep" value="<?php echo htmlspecialchars($profissional['cep']); ?>" required>
                            <div class="invalid-feedback">Por favor, informe o CEP.</div>
                        </div>
                    </div>
                    <div class="col-md-7">
                        <div class="form-group">
                            <label for="logradouro">Logradouro *</label>
                            <input type="text" class="form-control" id="logradouro" name="logradouro" value="<?php echo htmlspecialchars($profissional['logradouro']); ?>" required>
                            <div class="invalid-feedback">Por favor, informe o logradouro.</div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="numero">Número *</label>
                            <input type="text" class="form-control" id="numero" name="numero" value="<?php echo htmlspecialchars($profissional['numero']); ?>" required>
                            <div class="invalid-feedback">Por favor, informe o número.</div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="complemento">Complemento</label>
                            <input type="text" class="form-control" id="complemento" name="complemento" value="<?php echo htmlspecialchars($profissional['complemento']); ?>">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="bairro">Bairro *</label>
                            <input type="text" class="form-control" id="bairro" name="bairro" value="<?php echo htmlspecialchars($profissional['bairro']); ?>" required>
                            <div class="invalid-feedback">Por favor, informe o bairro.</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="cidade">Cidade *</label>
                            <input type="text" class="form-control" id="cidade" name="cidade" value="<?php echo htmlspecialchars($profissional['cidade']); ?>" required>
                            <div class="invalid-feedback">Por favor, informe a cidade.</div>
                        </div>
                    </div>
                    <div class="col-md-1">
                        <div class="form-group">
                            <label for="uf">UF *</label>
                            <select class="form-control" id="uf" name="uf" required>
                                <option value="">UF</option>
                                <?php
                                $ufs = ['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO'];
                                foreach ($ufs as $estado) {
                                    echo '<option value="'.$estado.'"'.($estado == $profissional['uf'] ? ' selected' : '').'>'.$estado.'</option>';
                                }
                                ?>
                            </select>
                            <div class="invalid-feedback">Por favor, selecione a UF.</div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="telefone">Telefone *</label>
                            <input type="text" class="form-control phone" id="telefone" name="telefone" value="<?php echo htmlspecialchars($profissional['telefone']); ?>" required>
                            <div class="invalid-feedback">Por favor, informe o telefone.</div>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="form-group">
                            <label for="email">E-mail</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($profissional['email']); ?>">
                            <div class="invalid-feedback">Por favor, informe um e-mail válido.</div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Serviços *</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="banhista" name="banhista" value="1" <?php echo $profissional['banhista'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="banhista">Banhista</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="tosador" name="tosador" value="1" <?php echo $profissional['tosador'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="tosador">Tosador</label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="observacoes">Observações</label>
                    <textarea class="form-control" id="observacoes" name="observacoes" rows="3"><?php echo htmlspecialchars($profissional['observacoes']); ?></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Salvar Alterações</button>
                <a href="listar.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Voltar</a>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
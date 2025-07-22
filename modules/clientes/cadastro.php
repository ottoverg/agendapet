<?php
// Arquivo: /agendapet/modules/clientes/cadastro.php

require_once '../../includes/config.php';
requireAdmin();

$page_title = 'Cadastrar Cliente';
$custom_js = 'clientes.js';
include '../../includes/header.php';

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
    $observacoes = filter_input(INPUT_POST, 'observacoes', FILTER_SANITIZE_STRING);
    
    // Validar CPF
    if (!validaCPF($cpf)) {
        $_SESSION['error_message'] = 'CPF inválido. Por favor, verifique o número digitado.';
    } else {
        // Verificar se CPF já existe
        $sql = "SELECT id FROM clientes WHERE cpf = ?";
        $exists = fetchOne($sql, [$cpf]);
        
        if ($exists) {
            $_SESSION['error_message'] = 'Já existe um cliente cadastrado com este CPF.';
        } else {
            // Inserir no banco de dados
            $sql = "INSERT INTO clientes (nome, cpf, cep, logradouro, numero, complemento, bairro, cidade, uf, telefone, email, observacoes, data_cadastro)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            $params = [$nome, $cpf, $cep, $logradouro, $numero, $complemento, $bairro, $cidade, $uf, $telefone, $email, $observacoes];
            
            try {
                executeQuery($sql, $params);
                $_SESSION['success_message'] = 'Cliente cadastrado com sucesso!';
                header('Location: ../clientes/listar.php');
                exit();
            } catch (PDOException $e) {
                $_SESSION['error_message'] = 'Erro ao cadastrar cliente: ' . $e->getMessage();
            }
        }
    }
}

// Função para validar CPF
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
            <h2><i class="fas fa-user-plus"></i> Cadastrar Novo Cliente</h2>
            <hr>
            
            <form method="POST" class="needs-validation" novalidate>
                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label for="nome">Nome Completo *</label>
                            <input type="text" class="form-control" id="nome" name="nome" required>
                            <div class="invalid-feedback">Por favor, informe o nome completo.</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="cpf">CPF *</label>
                            <input type="text" class="form-control cpf" id="cpf" name="cpf" required>
                            <div class="invalid-feedback">Por favor, informe um CPF válido.</div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="cep">CEP *</label>
                            <input type="text" class="form-control cep" id="cep" name="cep" required>
                            <div class="invalid-feedback">Por favor, informe o CEP.</div>
                        </div>
                    </div>
                    <div class="col-md-7">
                        <div class="form-group">
                            <label for="logradouro">Logradouro *</label>
                            <input type="text" class="form-control" id="logradouro" name="logradouro" required>
                            <div class="invalid-feedback">Por favor, informe o logradouro.</div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="numero">Número *</label>
                            <input type="text" class="form-control" id="numero" name="numero" required>
                            <div class="invalid-feedback">Por favor, informe o número.</div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="complemento">Complemento</label>
                            <input type="text" class="form-control" id="complemento" name="complemento">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="bairro">Bairro *</label>
                            <input type="text" class="form-control" id="bairro" name="bairro" required>
                            <div class="invalid-feedback">Por favor, informe o bairro.</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="cidade">Cidade *</label>
                            <input type="text" class="form-control" id="cidade" name="cidade" required>
                            <div class="invalid-feedback">Por favor, informe a cidade.</div>
                        </div>
                    </div>
                    <div class="col-md-1">
                        <div class="form-group">
                            <label for="uf">UF *</label>
                            <select class="form-control" id="uf" name="uf" required>
                                <option value="">UF</option>
                                <option value="AC">AC</option>
                                <option value="AL">AL</option>
                                <option value="AP">AP</option>
                                <option value="AM">AM</option>
                                <option value="BA">BA</option>
                                <option value="CE">CE</option>
                                <option value="DF">DF</option>
                                <option value="ES">ES</option>
                                <option value="GO">GO</option>
                                <option value="MA">MA</option>
                                <option value="MT">MT</option>
                                <option value="MS">MS</option>
                                <option value="MG">MG</option>
                                <option value="PA">PA</option>
                                <option value="PB">PB</option>
                                <option value="PR">PR</option>
                                <option value="PE">PE</option>
                                <option value="PI">PI</option>
                                <option value="RJ">RJ</option>
                                <option value="RN">RN</option>
                                <option value="RS">RS</option>
                                <option value="RO">RO</option>
                                <option value="RR">RR</option>
                                <option value="SC">SC</option>
                                <option value="SP">SP</option>
                                <option value="SE">SE</option>
                                <option value="TO">TO</option>
                            </select>
                            <div class="invalid-feedback">Por favor, selecione a UF.</div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="telefone">Telefone *</label>
                            <input type="text" class="form-control phone" id="telefone" name="telefone" required>
                            <div class="invalid-feedback">Por favor, informe o telefone.</div>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="form-group">
                            <label for="email">E-mail</label>
                            <input type="email" class="form-control" id="email" name="email">
                            <div class="invalid-feedback">Por favor, informe um e-mail válido.</div>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="observacoes">Observações</label>
                    <textarea class="form-control" id="observacoes" name="observacoes" rows="3"></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Salvar</button>
                <a href="../clientes/listar.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Voltar</a>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
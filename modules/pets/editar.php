<?php
require_once '../../includes/config.php';
requireAuth();

$page_title = 'Editar Pet';
$custom_js = 'pets.js';
include '../../includes/header.php';

$pet_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$pet_id) {
    header('Location: listar.php');
    exit();
}

// Obter dados do pet
$pet = fetchOne("SELECT * FROM pets WHERE id = ?", [$pet_id]);

if (!$pet) {
    $_SESSION['error_message'] = 'Pet não encontrado.';
    header('Location: listar.php');
    exit();
}

// Obter cliente do pet
$cliente = fetchOne("SELECT id, nome FROM clientes WHERE id = ?", [$pet['cliente_id']]);

// Obter raça do pet
$raca = fetchOne("SELECT id, nome FROM racas WHERE id = ?", [$pet['raca_id']]);

// Obter clientes para o select
$clientes = fetchAll("SELECT id, nome FROM clientes ORDER BY nome");

// Processar formulário se for submetido
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cliente_id = filter_input(INPUT_POST, 'cliente_id', FILTER_VALIDATE_INT);
    $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_STRING);
    $genero = filter_input(INPUT_POST, 'genero', FILTER_SANITIZE_STRING);
    $especie = filter_input(INPUT_POST, 'especie', FILTER_SANITIZE_STRING);
    $raca_id = filter_input(INPUT_POST, 'raca_id', FILTER_VALIDATE_INT);
    $peso = filter_input(INPUT_POST, 'peso', FILTER_SANITIZE_STRING);
    $tamanho = filter_input(INPUT_POST, 'tamanho', FILTER_SANITIZE_STRING);
    $pelagem = filter_input(INPUT_POST, 'pelagem', FILTER_SANITIZE_STRING);
    $data_nascimento = filter_input(INPUT_POST, 'data_nascimento', FILTER_SANITIZE_STRING);
    $observacoes = filter_input(INPUT_POST, 'observacoes', FILTER_SANITIZE_STRING);
    
    // Validar dados
    if (!$cliente_id || !$nome || !$genero || !$especie || !$raca_id || !$peso || !$tamanho || !$pelagem) {
        $_SESSION['error_message'] = 'Por favor, preencha todos os campos obrigatórios.';
    } else {
        // Converter peso para formato decimal
        $peso = str_replace(',', '.', $peso);
        $peso = (float)$peso;
        
        // Atualizar no banco de dados
        $sql = "UPDATE pets SET 
                cliente_id = ?, nome = ?, genero = ?, especie = ?, raca_id = ?, 
                data_nascimento = ?, peso = ?, tamanho = ?, pelagem = ?, observacoes = ?
                WHERE id = ?";
        
        try {
            executeQuery($sql, [$cliente_id, $nome, $genero, $especie, $raca_id, 
                              $data_nascimento, $peso, $tamanho, $pelagem, $observacoes, $pet_id]);
            $_SESSION['success_message'] = 'Pet atualizado com sucesso!';
            header('Location: listar.php');
            exit();
        } catch (PDOException $e) {
            $_SESSION['error_message'] = 'Erro ao atualizar pet: ' . $e->getMessage();
        }
    }
}
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <h2><i class="fas fa-paw"></i> Editar Pet</h2>
            <hr>
            
            <form method="POST" class="needs-validation" novalidate>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="cliente_id">Dono *</label>
                            <select class="form-control select2" id="cliente_id" name="cliente_id" required>
                                <option value="">Selecione o dono</option>
                                <?php foreach ($clientes as $c): ?>
                                    <option value="<?php echo $c['id']; ?>" <?php echo (isset($_POST['cliente_id']) ? $_POST['cliente_id'] : $pet['cliente_id']) == $c['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($c['nome']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">Por favor, selecione o dono.</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="nome">Nome do Pet *</label>
                            <input type="text" class="form-control" id="nome" name="nome" value="<?php echo isset($_POST['nome']) ? htmlspecialchars($_POST['nome']) : htmlspecialchars($pet['nome']); ?>" required>
                            <div class="invalid-feedback">Por favor, informe o nome do pet.</div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="genero">Gênero *</label>
                            <select class="form-control" id="genero" name="genero" required>
                                <option value="">Selecione</option>
                                <option value="Macho" <?php echo (isset($_POST['genero']) ? $_POST['genero'] : $pet['genero']) == 'Macho' ? 'selected' : ''; ?>>Macho</option>
                                <option value="Fêmea" <?php echo (isset($_POST['genero']) ? $_POST['genero'] : $pet['genero']) == 'Fêmea' ? 'selected' : ''; ?>>Fêmea</option>
                            </select>
                            <div class="invalid-feedback">Por favor, selecione o gênero.</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="especie">Espécie *</label>
                            <select class="form-control" id="especie" name="especie" required>
                                <option value="">Selecione</option>
                                <option value="Cachorro" <?php echo (isset($_POST['especie']) ? $_POST['especie'] : $pet['especie']) == 'Cachorro' ? 'selected' : ''; ?>>Cachorro</option>
                                <option value="Gato" <?php echo (isset($_POST['especie']) ? $_POST['especie'] : $pet['especie']) == 'Gato' ? 'selected' : ''; ?>>Gato</option>
                            </select>
                            <div class="invalid-feedback">Por favor, selecione a espécie.</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="raca_id">Raça *</label>
                            <select class="form-control select2" id="raca_id" name="raca_id" required>
                                <option value="">Selecione a raça</option>
                                <?php 
                                // Carregar raças baseadas na espécie selecionada
                                $especie_selecionada = isset($_POST['especie']) ? $_POST['especie'] : $pet['especie'];
                                if ($especie_selecionada) {
                                    $racas = fetchAll("SELECT id, nome FROM racas WHERE especie = ? ORDER BY nome", [$especie_selecionada]);
                                    foreach ($racas as $r) {
                                        echo '<option value="' . $r['id'] . '"' . 
                                             ((isset($_POST['raca_id']) ? $_POST['raca_id'] : $pet['raca_id']) == $r['id'] ? ' selected' : '') . 
                                             '>' . htmlspecialchars($r['nome']) . '</option>';
                                    }
                                }
                                ?>
                            </select>
                            <div class="invalid-feedback">Por favor, selecione a raça.</div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="peso">Peso (kg) *</label>
                            <input type="text" class="form-control" id="peso" name="peso" value="<?php echo isset($_POST['peso']) ? htmlspecialchars($_POST['peso']) : number_format($pet['peso'], 3, ',', ''); ?>" required>
                            <div class="invalid-feedback">Por favor, informe o peso.</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="tamanho">Tamanho *</label>
                            <select class="form-control" id="tamanho" name="tamanho" required>
                                <option value="">Selecione</option>
                                <option value="PP" <?php echo (isset($_POST['tamanho']) ? $_POST['tamanho'] : $pet['tamanho']) == 'PP' ? 'selected' : ''; ?>>PP</option>
                                <option value="P" <?php echo (isset($_POST['tamanho']) ? $_POST['tamanho'] : $pet['tamanho']) == 'P' ? 'selected' : ''; ?>>P</option>
                                <option value="M" <?php echo (isset($_POST['tamanho']) ? $_POST['tamanho'] : $pet['tamanho']) == 'M' ? 'selected' : ''; ?>>M</option>
                                <option value="G" <?php echo (isset($_POST['tamanho']) ? $_POST['tamanho'] : $pet['tamanho']) == 'G' ? 'selected' : ''; ?>>G</option>
                                <option value="GG" <?php echo (isset($_POST['tamanho']) ? $_POST['tamanho'] : $pet['tamanho']) == 'GG' ? 'selected' : ''; ?>>GG</option>
                            </select>
                            <div class="invalid-feedback">Por favor, selecione o tamanho.</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="pelagem">Pelagem *</label>
                            <select class="form-control" id="pelagem" name="pelagem" required>
                                <option value="">Selecione</option>
                                <option value="Curta" <?php echo (isset($_POST['pelagem']) ? $_POST['pelagem'] : $pet['pelagem']) == 'Curta' ? 'selected' : ''; ?>>Curta</option>
                                <option value="Longa" <?php echo (isset($_POST['pelagem']) ? $_POST['pelagem'] : $pet['pelagem']) == 'Longa' ? 'selected' : ''; ?>>Longa</option>
                            </select>
                            <div class="invalid-feedback">Por favor, selecione o tipo de pelagem.</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="data_nascimento">Data Nascimento</label>
                            <input type="date" class="form-control" id="data_nascimento" name="data_nascimento" value="<?php echo isset($_POST['data_nascimento']) ? htmlspecialchars($_POST['data_nascimento']) : ($pet['data_nascimento'] ? htmlspecialchars($pet['data_nascimento']) : ''); ?>">
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="observacoes">Observações</label>
                    <textarea class="form-control" id="observacoes" name="observacoes" rows="3"><?php echo isset($_POST['observacoes']) ? htmlspecialchars($_POST['observacoes']) : htmlspecialchars($pet['observacoes']); ?></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Salvar Alterações</button>
                <a href="listar.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Voltar</a>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Carregar raças quando a espécie mudar
    $('#especie').change(function() {
        const especie = $(this).val();
        if (especie) {
            $.getJSON('<?php echo SITE_URL; ?>/modules/racas/get_racas.php?especie=' + especie, function(data) {
                $('#raca_id').empty();
                $('#raca_id').append('<option value="">Selecione a raça</option>');
                $.each(data, function(key, value) {
                    $('#raca_id').append('<option value="' + value.id + '">' + value.nome + '</option>');
                });
            });
        } else {
            $('#raca_id').empty();
            $('#raca_id').append('<option value="">Selecione a raça</option>');
        }
    });
    
    // Máscara para o campo de peso
    $('#peso').mask('#0.000', {reverse: true});
});
</script>

<?php include '../../includes/footer.php'; ?>
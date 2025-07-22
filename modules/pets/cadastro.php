<?php
require_once '../../includes/config.php';
requireAuth();

$page_title = 'Cadastrar Pet';
$custom_js = ['pets.js'];
include '../../includes/header.php';

// Obter clientes para o select
$clientes = fetchAll("SELECT id, nome FROM clientes ORDER BY nome");

// Carregar todas as raças para fallback
$allRacas = fetchAll("SELECT id, nome, especie FROM racas ORDER BY especie, nome");

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
        
        // Inserir no banco de dados
        $sql = "INSERT INTO pets 
                (cliente_id, nome, genero, especie, raca_id, peso, tamanho, pelagem, data_nascimento, observacoes, data_cadastro)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        
        try {
            executeQuery($sql, [
                $cliente_id, 
                $nome, 
                $genero, 
                $especie, 
                $raca_id, 
                $peso, 
                $tamanho, 
                $pelagem,
                $data_nascimento ?: null,
                $observacoes
            ]);
            
            $_SESSION['success_message'] = 'Pet cadastrado com sucesso!';
            header('Location: listar.php');
            exit();
        } catch (PDOException $e) {
            $_SESSION['error_message'] = 'Erro ao cadastrar pet: ' . $e->getMessage();
        }
    }
}
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <h2><i class="fas fa-paw"></i> Cadastrar Novo Pet</h2>
            <hr>
            
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger">
                    <?php echo $_SESSION['error_message']; ?>
                    <?php unset($_SESSION['error_message']); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="needs-validation" novalidate>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="cliente_id">Dono *</label>
                            <select class="form-control select2" id="cliente_id" name="cliente_id" required>
                                <option value="">Selecione o dono</option>
                                <?php foreach ($clientes as $cliente): ?>
                                    <option value="<?php echo $cliente['id']; ?>" 
                                        <?php echo isset($_POST['cliente_id']) && $_POST['cliente_id'] == $cliente['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cliente['nome']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">Por favor, selecione o dono.</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="nome">Nome do Pet *</label>
                            <input type="text" class="form-control" id="nome" name="nome" 
                                   value="<?php echo isset($_POST['nome']) ? htmlspecialchars($_POST['nome']) : ''; ?>" required>
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
                                <option value="Macho" <?php echo isset($_POST['genero']) && $_POST['genero'] == 'Macho' ? 'selected' : ''; ?>>Macho</option>
                                <option value="Fêmea" <?php echo isset($_POST['genero']) && $_POST['genero'] == 'Fêmea' ? 'selected' : ''; ?>>Fêmea</option>
                            </select>
                            <div class="invalid-feedback">Por favor, selecione o gênero.</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="especie">Espécie *</label>
                            <select class="form-control" id="especie" name="especie" required>
                                <option value="">Selecione</option>
                                <option value="Cachorro" <?php echo isset($_POST['especie']) && $_POST['especie'] == 'Cachorro' ? 'selected' : ''; ?>>Cachorro</option>
                                <option value="Gato" <?php echo isset($_POST['especie']) && $_POST['especie'] == 'Gato' ? 'selected' : ''; ?>>Gato</option>
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
                                // Carrega raças se já tiver espécie selecionada (para fallback)
                                if (isset($_POST['especie'])) {
                                    foreach ($allRacas as $raca) {
                                        if ($raca['especie'] == $_POST['especie']) {
                                            $selected = (isset($_POST['raca_id']) && $_POST['raca_id'] == $raca['id']) ? 'selected' : '';
                                            echo '<option value="'.$raca['id'].'" '.$selected.'>'.htmlspecialchars($raca['nome']).'</option>';
                                        }
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
                            <input type="text" class="form-control" id="peso" name="peso" 
                                   value="<?php echo isset($_POST['peso']) ? htmlspecialchars($_POST['peso']) : ''; ?>" required>
                            <div class="invalid-feedback">Por favor, informe o peso.</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="tamanho">Tamanho *</label>
                            <select class="form-control" id="tamanho" name="tamanho" required>
                                <option value="">Selecione</option>
                                <option value="PP" <?php echo isset($_POST['tamanho']) && $_POST['tamanho'] == 'PP' ? 'selected' : ''; ?>>PP</option>
                                <option value="P" <?php echo isset($_POST['tamanho']) && $_POST['tamanho'] == 'P' ? 'selected' : ''; ?>>P</option>
                                <option value="M" <?php echo isset($_POST['tamanho']) && $_POST['tamanho'] == 'M' ? 'selected' : ''; ?>>M</option>
                                <option value="G" <?php echo isset($_POST['tamanho']) && $_POST['tamanho'] == 'G' ? 'selected' : ''; ?>>G</option>
                                <option value="GG" <?php echo isset($_POST['tamanho']) && $_POST['tamanho'] == 'GG' ? 'selected' : ''; ?>>GG</option>
                            </select>
                            <div class="invalid-feedback">Por favor, selecione o tamanho.</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="pelagem">Pelagem *</label>
                            <select class="form-control" id="pelagem" name="pelagem" required>
                                <option value="">Selecione</option>
                                <option value="Curta" <?php echo isset($_POST['pelagem']) && $_POST['pelagem'] == 'Curta' ? 'selected' : ''; ?>>Curta</option>
                                <option value="Longa" <?php echo isset($_POST['pelagem']) && $_POST['pelagem'] == 'Longa' ? 'selected' : ''; ?>>Longa</option>
                            </select>
                            <div class="invalid-feedback">Por favor, selecione o tipo de pelagem.</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="data_nascimento">Data Nascimento</label>
                            <input type="date" class="form-control" id="data_nascimento" name="data_nascimento" 
                                   value="<?php echo isset($_POST['data_nascimento']) ? htmlspecialchars($_POST['data_nascimento']) : ''; ?>">
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="observacoes">Observações</label>
                    <textarea class="form-control" id="observacoes" name="observacoes" rows="3"><?php 
                        echo isset($_POST['observacoes']) ? htmlspecialchars($_POST['observacoes']) : ''; 
                    ?></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Salvar</button>
                <a href="listar.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Voltar</a>
            </form>
        </div>
    </div>
</div>

<script>
// Verificar se jQuery está carregado
function checkJQuery() {
    if (window.jQuery === undefined) {
        console.error('jQuery não foi carregado corretamente');
        return false;
    }
    return true;
}

// Esperar até que tudo esteja carregado
document.addEventListener('DOMContentLoaded', function() {
    if (!checkJQuery()) return;
    
    // Usar jQuery no modo seguro
    (function($) {
        // Verificar se o plugin de máscara está disponível
        if (typeof $.fn.mask === 'undefined') {
            console.error('jQuery Mask plugin não está disponível');
            return;
        }

        // Aplicar máscara ao campo de peso
        $('#peso').mask('#0.000', {reverse: true});

        // Carregar raças dinamicamente
        $('#especie').on('change', function() {
            var especie = $(this).val();
            var $racaSelect = $('#raca_id');

            if (!especie) {
                $racaSelect.empty().append('<option value="">Selecione a raça</option>');
                return;
            }

            $racaSelect.empty().append('<option value="">Carregando...</option>');

            $.ajax({
                url: '<?php echo SITE_URL; ?>/modules/racas/get_racas.php',
                type: 'GET',
                dataType: 'json',
                data: { especie: especie },
                success: function(response) {
                    $racaSelect.empty();
                    $racaSelect.append('<option value="">Selecione a raça</option>');
                    
                    if (response.success && response.data) {
                        $.each(response.data, function(index, raca) {
                            $racaSelect.append(
                                $('<option></option>').val(raca.id).text(raca.nome)
                            );
                        });
                    }
                    
                    // Selecionar valor anterior se existir
                    <?php if (isset($_POST['raca_id'])): ?>
                        $racaSelect.val(<?php echo $_POST['raca_id']; ?>);
                    <?php endif; ?>
                },
                error: function(xhr, status, error) {
                    console.error('Erro ao carregar raças:', status, error);
                    
                    // Fallback: carrega todas as raças e filtra localmente
                    let options = '<option value="">Selecione a raça</option>';
                    <?php foreach ($allRacas as $raca): ?>
                        if ('<?php echo $raca['especie']; ?>' === especie) {
                            options += '<option value="<?php echo $raca['id']; ?>" <?php echo isset($_POST['raca_id']) && $_POST['raca_id'] == $raca['id'] ? "selected" : ""; ?>><?php echo htmlspecialchars($raca['nome']); ?></option>';
                        }
                    <?php endforeach; ?>
                    
                    $racaSelect.html(options || '<option value="">Nenhuma raça encontrada</option>');
                }
            });
        });

        // Disparar change se já tiver espécie selecionada
        <?php if (isset($_POST['especie'])): ?>
            $('#especie').trigger('change');
        <?php endif; ?>
    })(jQuery);
});
</script>

<?php include '../../includes/footer.php'; ?>
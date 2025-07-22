<?php
require_once '../../includes/config.php';
requireAuth();

$page_title = 'Editar Agendamento';
$custom_js = 'agendamentos.js';
include '../../includes/header.php';

$agendamento_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$agendamento_id) {
    header('Location: listar.php');
    exit();
}

// Obter dados do agendamento
$agendamento = fetchOne("SELECT * FROM agendamentos WHERE id = ?", [$agendamento_id]);

if (!$agendamento) {
    $_SESSION['error_message'] = 'Agendamento não encontrado.';
    header('Location: listar.php');
    exit();
}

// Obter dados para os selects
$clientes = fetchAll("SELECT id, nome FROM clientes ORDER BY nome");
$pets = fetchAll("SELECT id, nome FROM pets WHERE cliente_id = ? ORDER BY nome", [$agendamento['cliente_id']]);
$servicos = fetchAll("SELECT id, nome, duracao, preco FROM servicos WHERE ativo = 1 ORDER BY nome");
$profissionais = fetchAll("SELECT id, nome FROM profissionais ORDER BY nome");

// Processar formulário se for submetido
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cliente_id = filter_input(INPUT_POST, 'cliente_id', FILTER_VALIDATE_INT);
    $pet_id = filter_input(INPUT_POST, 'pet_id', FILTER_VALIDATE_INT);
    $servico_id = filter_input(INPUT_POST, 'servico_id', FILTER_VALIDATE_INT);
    $profissional_id = filter_input(INPUT_POST, 'profissional_id', FILTER_VALIDATE_INT);
    $data_agendamento = filter_input(INPUT_POST, 'data_agendamento', FILTER_SANITIZE_STRING);
    $hora = filter_input(INPUT_POST, 'hora', FILTER_SANITIZE_STRING);
    $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);
    $observacoes = filter_input(INPUT_POST, 'observacoes', FILTER_SANITIZE_STRING);
    
    // Validar dados
    if (!$cliente_id || !$pet_id || !$servico_id || !$profissional_id || !$data_agendamento || !$hora || !$status) {
        $_SESSION['error_message'] = 'Por favor, preencha todos os campos obrigatórios.';
    } else {
        // Verificar disponibilidade do profissional (exceto para este agendamento)
        $sql = "SELECT id FROM agendamentos 
                WHERE profissional_id = ? 
                AND data_agendamento = ? 
                AND hora = ? 
                AND id != ?";
        $existing = fetchOne($sql, [$profissional_id, $data_agendamento, $hora, $agendamento_id]);
        
        if ($existing) {
            $_SESSION['error_message'] = 'O profissional já possui um agendamento neste horário.';
        } else {
            // Obter duração do serviço
            $sql_servico = "SELECT duracao FROM servicos WHERE id = ?";
            $servico = fetchOne($sql_servico, [$servico_id]);
            $duracao = $servico['duracao']; // em minutos
            
            // Calcular hora final
            $hora_final = date('H:i', strtotime("$hora + $duracao minutes"));
            
            // Atualizar agendamento
            $sql = "UPDATE agendamentos SET 
                    cliente_id = ?, pet_id = ?, servico_id = ?, profissional_id = ?, 
                    data_agendamento = ?, hora = ?, hora_final = ?, status = ?, observacoes = ?
                    WHERE id = ?";
            
            try {
                executeQuery($sql, [$cliente_id, $pet_id, $servico_id, $profissional_id, 
                                  $data_agendamento, $hora, $hora_final, $status, $observacoes, $agendamento_id]);
                $_SESSION['success_message'] = 'Agendamento atualizado com sucesso!';
                header('Location: listar.php');
                exit();
            } catch (PDOException $e) {
                $_SESSION['error_message'] = 'Erro ao atualizar agendamento: ' . $e->getMessage();
            }
        }
    }
}
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <h2><i class="far fa-calendar-alt"></i> Editar Agendamento</h2>
            <hr>
            
            <form method="POST" id="agendamentoForm">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="cliente_id">Cliente *</label>
                            <select class="form-control select2" id="cliente_id" name="cliente_id" required>
                                <option value="">Selecione um cliente</option>
                                <?php foreach ($clientes as $cliente): ?>
                                    <option value="<?php echo $cliente['id']; ?>" <?php echo (isset($_POST['cliente_id']) ? $_POST['cliente_id'] : $agendamento['cliente_id']) == $cliente['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cliente['nome']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="pet_id">Pet *</label>
                            <select class="form-control select2" id="pet_id" name="pet_id" required>
                                <option value="">Selecione um pet</option>
                                <?php foreach ($pets as $pet): ?>
                                    <option value="<?php echo $pet['id']; ?>" <?php echo (isset($_POST['pet_id']) ? $_POST['pet_id'] : $agendamento['pet_id']) == $pet['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($pet['nome']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="servico_id">Serviço *</label>
                            <select class="form-control select2" id="servico_id" name="servico_id" required>
                                <option value="">Selecione um serviço</option>
                                <?php foreach ($servicos as $servico): ?>
                                    <option value="<?php echo $servico['id']; ?>" data-duracao="<?php echo $servico['duracao']; ?>" data-preco="<?php echo $servico['preco']; ?>" <?php echo (isset($_POST['servico_id']) ? $_POST['servico_id'] : $agendamento['servico_id']) == $servico['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($servico['nome']); ?> (<?php echo $servico['duracao']; ?> min)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="profissional_id">Profissional *</label>
                            <select class="form-control select2" id="profissional_id" name="profissional_id" required>
                                <option value="">Selecione um profissional</option>
                                <?php foreach ($profissionais as $profissional): ?>
                                    <option value="<?php echo $profissional['id']; ?>" <?php echo (isset($_POST['profissional_id']) ? $_POST['profissional_id'] : $agendamento['profissional_id']) == $profissional['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($profissional['nome']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="data_agendamento">Data *</label>
                            <input type="date" class="form-control" id="data_agendamento" name="data_agendamento" value="<?php echo isset($_POST['data_agendamento']) ? htmlspecialchars($_POST['data_agendamento']) : htmlspecialchars($agendamento['data_agendamento']); ?>" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="hora">Hora *</label>
                            <select class="form-control" id="hora" name="hora" required>
                                <option value="">Selecione a hora</option>
                                <?php
                                $start = strtotime('08:00');
                                $end = strtotime('18:00');
                                $interval = 15 * 60; // 15 minutos em segundos
                                
                                for ($i = $start; $i <= $end; $i += $interval) {
                                    $time = date('H:i', $i);
                                    $selected = (isset($_POST['hora']) ? $_POST['hora'] : substr($agendamento['hora'], 0, 5)) == $time ? 'selected' : '';
                                    echo '<option value="' . $time . '" ' . $selected . '>' . $time . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="hora_final">Hora Final</label>
                            <input type="text" class="form-control" id="hora_final" name="hora_final" value="<?php echo isset($_POST['hora_final']) ? htmlspecialchars($_POST['hora_final']) : substr($agendamento['hora_final'], 0, 5); ?>" readonly>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="status">Status *</label>
                            <select class="form-control" id="status" name="status" required>
                                <option value="">Selecione o status</option>
                                <option value="agendado" <?php echo (isset($_POST['status']) ? $_POST['status'] : $agendamento['status']) == 'agendado' ? 'selected' : ''; ?>>Agendado</option>
                                <option value="confirmado" <?php echo (isset($_POST['status']) ? $_POST['status'] : $agendamento['status']) == 'confirmado' ? 'selected' : ''; ?>>Confirmado</option>
                                <option value="concluido" <?php echo (isset($_POST['status']) ? $_POST['status'] : $agendamento['status']) == 'concluido' ? 'selected' : ''; ?>>Concluído</option>
                                <option value="cancelado" <?php echo (isset($_POST['status']) ? $_POST['status'] : $agendamento['status']) == 'cancelado' ? 'selected' : ''; ?>>Cancelado</option>
                                <option value="falta" <?php echo (isset($_POST['status']) ? $_POST['status'] : $agendamento['status']) == 'falta' ? 'selected' : ''; ?>>Falta</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="observacoes">Observações</label>
                    <textarea class="form-control" id="observacoes" name="observacoes" rows="3"><?php echo isset($_POST['observacoes']) ? htmlspecialchars($_POST['observacoes']) : htmlspecialchars($agendamento['observacoes']); ?></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Salvar Alterações</button>
                <a href="listar.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Voltar</a>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Calcular hora final quando serviço ou hora mudar
    $('#servico_id, #hora').change(function() {
        const duracao = $('#servico_id option:selected').data('duracao');
        const hora = $('#hora').val();
        
        if (duracao && hora) {
            const horaParts = hora.split(':');
            const date = new Date();
            date.setHours(parseInt(horaParts[0]));
            date.setMinutes(parseInt(horaParts[1]));
            date.setSeconds(0);
            
            date.setMinutes(date.getMinutes() + parseInt(duracao));
            
            const horaFinal = ('0' + date.getHours()).slice(-2) + ':' + ('0' + date.getMinutes()).slice(-2);
            $('#hora_final').val(horaFinal);
        } else {
            $('#hora_final').val('');
        }
    });
    
    // Carregar pets do cliente selecionado
    $('#cliente_id').change(function() {
        const clienteId = $(this).val();
        if (clienteId) {
            $.getJSON('<?php echo SITE_URL; ?>/modules/pets/get_pets.php?cliente_id=' + clienteId, function(data) {
                $('#pet_id').empty();
                $('#pet_id').append('<option value="">Selecione um pet</option>');
                $.each(data, function(key, value) {
                    $('#pet_id').append('<option value="' + value.id + '">' + value.nome + '</option>');
                });
            });
        } else {
            $('#pet_id').empty();
            $('#pet_id').append('<option value="">Selecione um pet</option>');
        }
    });
    
    // Verificar disponibilidade do profissional
    $('#profissional_id, #data_agendamento, #hora').change(function() {
        const profissionalId = $('#profissional_id').val();
        const data = $('#data_agendamento').val();
        const hora = $('#hora').val();
        
        if (profissionalId && data && hora) {
            $.getJSON('<?php echo SITE_URL; ?>/modules/agendamentos/check_disponibilidade.php', {
                profissional_id: profissionalId,
                data_agendamento: data,
                hora: hora,
                agendamento_id: <?php echo $agendamento_id; ?>
            }, function(response) {
                if (response.disponivel) {
                    $('#hora').removeClass('is-invalid');
                    $('#hora').addClass('is-valid');
                    $('#hora').next('.invalid-feedback').remove();
                } else {
                    $('#hora').removeClass('is-valid');
                    $('#hora').addClass('is-invalid');
                    if (!$('#hora').next('.invalid-feedback').length) {
                        $('#hora').after('<div class="invalid-feedback">O profissional já possui um agendamento neste horário.</div>');
                    }
                }
            });
        }
    });
    
    // Calcular hora final ao carregar a página
    if ($('#servico_id').val() && $('#hora').val()) {
        $('#servico_id').trigger('change');
    }
});
</script>

<?php include '../../includes/footer.php'; ?>
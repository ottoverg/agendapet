/**
 * Scripts principais do sistema Agendapet
 * Versão compatível com jQuery 3.6.0+
 */

// Usar jQuery no-conflict para evitar problemas
(function($) {
    'use strict';
    
    // Aguarda o DOM estar pronto
    $(document).ready(function() {
        console.log('Script.js iniciado - jQuery v'+$.fn.jquery);
        
        // =============================================
        // MÁSCARAS DE FORMULÁRIO
        // =============================================
        if(typeof $.fn.mask === 'function') {
            // Máscara para peso (formato 0.000)
            $('body').on('focus', '#peso', function() {
                $(this).mask('#0.000', {
                    reverse: true,
                    placeholder: "0.000"
                });
            });
            
            // Outras máscaras do sistema
            $('.cpf').mask('000.000.000-00');
            $('.phone').mask('(00) 00000-0000');
            $('.money').mask('000.000.000.000.000,00', {reverse: true});
            $('.cep').mask('00000-000');
        }
        
        // =============================================
        // CARREGAMENTO DINÂMICO DE RAÇAS
        // =============================================
        $('body').on('change', '#especie', function() {
            const especie = $(this).val();
            const $racaSelect = $('#raca_id');
            
            if(!especie) {
                $racaSelect.html('<option value="">Selecione a raça</option>');
                return;
            }
            
            $racaSelect.html('<option value="">Carregando raças...</option>');
            
            $.ajax({
                url: SITE_URL + '/modules/racas/get_racas.php',
                type: 'GET',
                dataType: 'json',
                data: { especie: especie },
                success: function(response) {
                    if(response.success && response.data) {
                        let options = '<option value="">Selecione a raça</option>';
                        
                        response.data.forEach(function(raca) {
                            options += `<option value="${raca.id}">${raca.nome}</option>`;
                        });
                        
                        $racaSelect.html(options);
                    }
                },
                error: function() {
                    $racaSelect.html('<option value="">Erro ao carregar</option>');
                }
            });
        });
    });
})(jQuery.noConflict());
// Verificar se jQuery está carregado
if (typeof jQuery === 'undefined') {
    console.error('jQuery is required and must be loaded first');
} else {
    // Usar no-conflict mode
    (function($) {
        $(document).ready(function() {
            // Verificar se jQuery Mask está disponível
            if (typeof $.fn.mask === 'function') {
                // Aplicar máscara ao campo de peso
                $('#peso').mask('#0.000', {reverse: true});
            } else {
                console.error('jQuery Mask plugin is not loaded');
            }

            // Validação de formulário
            $('form.needs-validation').on('submit', function(e) {
                if (this.checkValidity() === false) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                $(this).addClass('was-validated');
            });
        });
    })(jQuery);
}
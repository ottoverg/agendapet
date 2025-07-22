$(document).ready(function() {
    // Máscara para preço
    $('.money').mask('#.##0,00', {reverse: true});
    
    // Validação de formulário
    $('form.needs-validation').on('submit', function(e) {
        if (this.checkValidity() === false) {
            e.preventDefault();
            e.stopPropagation();
        }
        $(this).addClass('was-validated');
    });
});
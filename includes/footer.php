<?php
// Arquivo: /agendapet/includes/footer.php
?>
        </div> <!-- Fecha container -->
    </div> <!-- Fecha main-content -->

    <footer class="footer mt-auto py-3 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <span class="text-muted">&copy; <?php echo date('Y'); ?> Pets Place - Todos os direitos reservados</span>
                </div>
                <div class="col-md-6 text-right">
                    <span class="text-muted">Vers���o 1.0.0</span>
                </div>
            </div>
        </div>
    </footer>

    <!-- Carregar jQuery primeiro -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Carregar jQuery Mask Plugin do CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    
    <!-- Carregar Bootstrap JS -->
    <script src="<?php echo ASSETS_PATH; ?>/js/bootstrap.min.js"></script>
    
    <!-- Carregar scripts personalizados -->
    <?php if (isset($custom_js) && is_array($custom_js)): ?>
    <?php foreach ($custom_js as $js_file): ?>
        <script src="<?php echo ASSETS_PATH; ?>/js/<?php echo htmlspecialchars($js_file); ?>"></script>
    <?php endforeach; ?>
<?php endif; 
?>
    
    <!-- Carregar script principal -->
    <script src="<?php echo ASSETS_PATH; ?>/js/script.js"></script>
</body>
</html>
</main> <div id="custom-modal" class="modal-overlay" style="display: none;">
    <div class="modal-content">
        <h2 id="modal-title">Título do Modal</h2>
        <p id="modal-message">Mensagem do modal.</p>
        <div class="modal-buttons">
            <button id="modal-btn-primary" class="btn">OK</button>
            <button id="modal-btn-secondary" class="btn" style="display: none;">Cancelar</button>
        </div>
    </div>
</div>

<script src="js/script.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const userMenuButton = document.getElementById('user-menu-button');
    const userDropdownMenu = document.getElementById('user-dropdown-menu');

    if (userMenuButton && userDropdownMenu) {
        userMenuButton.addEventListener('click', function(event) {
            event.stopPropagation(); // Impede que o clique feche o menu imediatamente
            userDropdownMenu.classList.toggle('visible');
            userMenuButton.classList.toggle('active');
        });

        // Fecha o menu se clicar em qualquer outro lugar da tela
        document.addEventListener('click', function() {
            if (userDropdownMenu.classList.contains('visible')) {
                userDropdownMenu.classList.remove('visible');
                userMenuButton.classList.remove('active');
            }
        });
    }
});
</script>

</body>
</html>
    </main>

    <!-- Bootstrap JS Bundle (requiere Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- FullCalendar JS (si se necesita, pero no obligatorio para todas las páginas) -->
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/es.js'></script>
    <!-- Script para colapsar sidebar en móviles -->
    <script>
        document.querySelector('[data-bs-toggle="collapse"]')?.addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('show');
        });
        // Si existe la función mostrarMensaje (toast), se puede definir aquí
    </script>
    <?php if(isset($_SESSION['mensaje'])): ?>
    <script>
        // Simple alert para mostrar mensajes (puedes mejorarlo con toast)
        alert('<?php echo addslashes($_SESSION['mensaje']); ?>');
    </script>
    <?php unset($_SESSION['mensaje']); endif; ?>
</body>
</html>
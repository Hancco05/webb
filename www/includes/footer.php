    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/es.js"></script>
    <script>
        // Toggle sidebar en móviles (agregar un botón con clase .sidebar-toggle en la barra superior)
        document.querySelector('.sidebar-toggle')?.addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('show');
        });
    </script>
    <?php if(isset($_SESSION['mensaje'])): ?>
        <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
        <button class="btn btn-sm btn-outline-secondary d-md-none sidebar-toggle me-2" type="button">
            <i class="bi bi-list"></i> Menú
        </button>    
        <div class="toast show" role="alert" data-bs-autohide="true" data-bs-delay="3000">
                <div class="toast-header"><strong>Notificación</strong><button type="button" class="btn-close" data-bs-dismiss="toast"></button></div>
                <div class="toast-body"><?= htmlspecialchars($_SESSION['mensaje']); unset($_SESSION['mensaje']); ?></div>
            </div>
        </div>
        <script>setTimeout(() => document.querySelector('.toast')?.remove(), 3000);</script>
    <?php endif; ?>
</body>
</html>
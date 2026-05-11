            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Modo oscuro
    const toggleButton = document.getElementById('darkModeToggle');
    const currentMode = localStorage.getItem('darkMode');
    if (currentMode === 'enabled') {
        document.body.classList.add('dark-mode');
        toggleButton.innerHTML = '<i class="bi bi-sun"></i> Modo claro';
    }
    toggleButton.addEventListener('click', () => {
        document.body.classList.toggle('dark-mode');
        if (document.body.classList.contains('dark-mode')) {
            localStorage.setItem('darkMode', 'enabled');
            toggleButton.innerHTML = '<i class="bi bi-sun"></i> Modo claro';
        } else {
            localStorage.setItem('darkMode', 'disabled');
            toggleButton.innerHTML = '<i class="bi bi-moon"></i> Modo oscuro';
        }
    });
</script>
</body>
</html>
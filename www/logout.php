<?php
// www/logout.php
//session_start();
//session_unset();
//session_destroy();
//header('Location: /index.php');
//exit;

<?php
session_start();
if (isset($_SESSION['user_id'])) {
    // Registrar el cierre de sesión (si implementaste logs)
    require_once 'includes/db.php';
    registrarLog($_SESSION['user_id'], 'logout', null, null, "Cierre de sesión");
}
session_destroy();
header('Location: index.php');
exit;

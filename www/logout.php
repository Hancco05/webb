<?php
session_start();
if (isset($_SESSION['user_id'])) {
    require_once 'includes/db.php';
    registrarLog($_SESSION['user_id'], 'logout', null, null, "Cierre de sesión");
}
session_destroy();
header('Location: /index.php');
exit;
<?php
require_once '../includes/db.php';
session_start();
if (!isset($_SESSION['user_id'])) exit;
$q = $_GET['q'] ?? '';
if (strlen($q) < 2) { echo json_encode([]); exit; }
$usuarios = buscarUsuarios($q, $_SESSION['user_id']);
echo json_encode($usuarios);
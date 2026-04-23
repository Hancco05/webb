<?php
// www/includes/auth.php

function requireLogin(): void {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (empty($_SESSION['usuario_id'])) {
        header('Location: /index.php');
        exit;
    }
}

function requireRole(string|array $roles): void {
    requireLogin();
    $roles = (array) $roles;
    if (!in_array($_SESSION['rol'], $roles, true)) {
        http_response_code(403);
        die('<h1>403 — Acceso denegado</h1><p>No tienes permisos para esta sección.</p><a href="/index.php">Volver</a>');
    }
}

function isLoggedIn(): bool {
    if (session_status() === PHP_SESSION_NONE) session_start();
    return !empty($_SESSION['usuario_id']);
}

function currentUser(): array {
    return [
        'id'     => $_SESSION['usuario_id']  ?? null,
        'nombre' => $_SESSION['nombre']       ?? '',
        'email'  => $_SESSION['email']        ?? '',
        'rol'    => $_SESSION['rol']          ?? '',
    ];
}

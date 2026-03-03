<?php
// ============================================================
// backend/middleware/auth.php
// Gestión de sesiones y comprobación de roles
// ============================================================

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/../config/config.php';

function startSecureSession(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_name(SESSION_NAME);
        session_set_cookie_params([
            'lifetime' => SESSION_LIFETIME,
            'path'     => '/',
            'secure'   => false,    // ← true en producción con HTTPS
            'httponly' => true,
            'samesite' => 'Strict',
        ]);
        session_start();
    }
}

/** Requiere que el usuario esté autenticado. Devuelve los datos de sesión. */
function requireAuth(): array {
    startSecureSession();
    if (empty($_SESSION['user'])) {
        respondUnauthorized('Sesión no iniciada o expirada');
    }
    return $_SESSION['user'];
}

/** Requiere rol admin; aborta con 403 si no lo tiene. */
function requireAdmin(): array {
    return requireAuth();
}

/** Inicia sesión almacenando los datos del usuario. */
function loginUser(array $user): void {
    startSecureSession();
    session_regenerate_id(true);  // previene session fixation
    $_SESSION['user'] = [
        'id'    => $user['id'],
        'name'  => $user['name'],
        'email' => $user['email'],
        'role'  => $user['role'],
    ];
}

/** Destruye la sesión. */
function logoutUser(): void {
    startSecureSession();
    $_SESSION = [];
    session_destroy();
}
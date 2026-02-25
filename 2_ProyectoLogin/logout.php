<?php
session_start();

// Solo aceptar solicitudes POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Token CSRF inválido.");
    }

    // Limpiar todas las variables de sesión
    $_SESSION = array();

    // Destruir la sesión
    session_destroy();

    // Redirigir al login
    header("location: index.php");
    exit();
} else {
    // Si intentan acceder directamente, redirigir
    header("location: index.php");
    exit();
}
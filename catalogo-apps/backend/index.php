<?php
// ============================================================
// backend/index.php  — Front Controller / Router
// Punto de entrada único para toda la API REST
// ============================================================

// --- Cabeceras comunes ---
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Responder a preflight CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

require_once __DIR__ . '/middleware/helpers.php';
require_once __DIR__ . '/middleware/auth.php';
require_once __DIR__ . '/config/Database.php';

// ---- Controladores ----
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/UserController.php';
require_once __DIR__ . '/controllers/ProjectController.php';
require_once __DIR__ . '/controllers/TechnologyController.php';

// ---- Enrutamiento simple basado en PATH_INFO ----
$method = $_SERVER['REQUEST_METHOD'];
// Obtener la ruta después de /backend/index.php
$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = preg_replace('#^.*?/backend#', '', $uri);
$uri    = rtrim($uri, '/') ?: '/';

// Segmentos: /resource/id/sub
$parts = explode('/', ltrim($uri, '/'));
$resource = $parts[0] ?? '';
$id       = isset($parts[1]) && is_numeric($parts[1]) ? (int)$parts[1] : null;

// ---- Tabla de rutas ----
match(true) {

    // Autenticación
    $resource === 'auth' && $parts[1] === 'login'  && $method === 'POST'   => AuthController::login(),
    $resource === 'auth' && $parts[1] === 'logout' && $method === 'POST'   => AuthController::logout(),
    $resource === 'auth' && $parts[1] === 'me'     && $method === 'GET'    => AuthController::me(),

    // Usuarios
    $resource === 'users' && $method === 'GET'  && $id === null  => UserController::index(),
    $resource === 'users' && $method === 'GET'  && $id !== null  => UserController::show($id),
    $resource === 'users' && $method === 'POST'                  => UserController::store(),
    $resource === 'users' && $method === 'PUT'  && $id !== null  => UserController::update($id),
    $resource === 'users' && $method === 'DELETE' && $id !== null => UserController::destroy($id),

    // Proyectos
    $resource === 'projects' && $method === 'GET'  && $id === null  => ProjectController::index(),
    $resource === 'projects' && $method === 'GET'  && $id !== null  => ProjectController::show($id),
    $resource === 'projects' && $method === 'POST'                   => ProjectController::store(),
    $resource === 'projects' && $method === 'PUT'  && $id !== null  => ProjectController::update($id),
    $resource === 'projects' && $method === 'DELETE' && $id !== null => ProjectController::destroy($id),

    // Tecnologías
    $resource === 'technologies' && $method === 'GET'  && $id === null  => TechnologyController::index(),
    $resource === 'technologies' && $method === 'GET'  && $id !== null  => TechnologyController::show($id),
    $resource === 'technologies' && $method === 'POST'                   => TechnologyController::store(),
    $resource === 'technologies' && $method === 'PUT'  && $id !== null  => TechnologyController::update($id),
    $resource === 'technologies' && $method === 'DELETE' && $id !== null => TechnologyController::destroy($id),

    // Ruta no encontrada
    default => respondNotFound("Ruta [{$method} /{$resource}] no existe"),
};
<?php
// ============================================================
// backend/config/config.php
// Configuración central de la aplicación
// ============================================================

// --- Base de datos ---
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'catalogo_apps');
define('DB_USER', 'root');          // ← cambiar en producción
define('DB_PASS', '');              // ← cambiar en producción
define('DB_CHARSET', 'utf8mb4');

// --- Sesión ---
define('SESSION_LIFETIME', 7200);   // 2 horas en segundos
define('SESSION_NAME', 'CATALOGO_SESSION');

// --- Paginación ---
define('PAGE_SIZE', 20);

// --- Entorno ---
define('APP_ENV', 'development');   // 'production' | 'development'
define('APP_DEBUG', APP_ENV === 'development');

// --- CORS (ajustar según dominio real en producción) ---
define('ALLOWED_ORIGIN', '*');
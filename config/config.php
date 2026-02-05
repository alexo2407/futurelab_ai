<?php 
// 1. Detectar Protocolo (HTTP o HTTPS)
$isSecure = false;
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    $isSecure = true;
} elseif (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' || !empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on') {
    $isSecure = true;
}

$protocol = $isSecure ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];

// Limpiar la carpeta del proyecto (útil para local y producción)
$script_name = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
$base_folder = preg_replace('/\/config$/', '', rtrim($script_name, '/\\'));

define('BASE_URL', $protocol . $host . $base_folder);

// Rutas Físicas (Basado en tu captura de VS Code)
define('ROOT_PATH', dirname(__DIR__));
define('STORAGE_PATH', ROOT_PATH . '/storage');
// 3. URLs para Assets (CSS/JS)
define('VISTA_URL', BASE_URL . '/vista');

// Configuración de Base de Datos
define('DB_HOST', 'localhost');
define('DB_SCHEMA', 'futurelab_ai');
define('DB_USER', 'root');
define('DB_PASSWORD', '');

// Configuración de Zona Horaria
date_default_timezone_set('America/Guatemala');

// Configuración de API Gemini
// IMPORTANTE: Reemplaza con tu API key real de Google Gemini
define('GEMINI_API_KEY', 'TU_API_KEY_AQUI');
define('GEMINI_API_URL', 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent');

// Rutas de Almacenamiento
// dirname(__DIR__) sube un nivel desde la carpeta 'config' a la raíz de tu app

define('RESULTS_PATH', STORAGE_PATH . '/results');
define('QR_PATH', STORAGE_PATH . '/qr');

define('UPLOADS_PATH', STORAGE_PATH . '/uploads');
define('STORAGE_URL', BASE_URL . '/storage');

// Configuración de Uploads
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/jpg']);

// Configuración de Sesión
define('SESSION_NAME', 'futurelab_session');
define('SESSION_LIFETIME', 3600 * 8); // 8 horas
<?php 

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
define('STORAGE_PATH', __DIR__ . '/../storage');
define('UPLOADS_PATH', STORAGE_PATH . '/uploads');
define('RESULTS_PATH', STORAGE_PATH . '/results');
define('QR_PATH', STORAGE_PATH . '/qr');

// URLs públicas para archivos
define('BASE_URL', 'http://localhost/futurelab-ai');
define('STORAGE_URL', BASE_URL . '/storage');

// Configuración de Uploads
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/jpg']);

// Configuración de Sesión
define('SESSION_NAME', 'futurelab_session');
define('SESSION_LIFETIME', 3600 * 8); // 8 horas
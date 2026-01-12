<?php
/**
 * Front Controller y Router Principal
 * FutureLab AI - Sistema de Eventos con IA
 */

// Cargar configuración
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/auth.php';

// Cargar modelos
require_once __DIR__ . '/modelo/conexion.php';
require_once __DIR__ . '/modelo/UsuarioModel.php';
require_once __DIR__ . '/modelo/RolModel.php';
require_once __DIR__ . '/modelo/CarreraModel.php';
require_once __DIR__ . '/modelo/ParticipanteModel.php';
require_once __DIR__ . '/modelo/AuditLogModel.php';

// Cargar controladores
require_once __DIR__ . '/controlador/AuthControlador.php';
require_once __DIR__ . '/controlador/ParticipanteControlador.php';
require_once __DIR__ . '/controlador/WallControlador.php';
require_once __DIR__ . '/controlador/AdminParticipantesControlador.php';
require_once __DIR__ . '/controlador/ConfigControlador.php';
require_once __DIR__ . '/controlador/CarreraControlador.php';

// Obtener la ruta solicitada
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Remover query string
$requestUri = strtok($requestUri, '?');

// Remover el prefijo del proyecto si existe
$basePath = '/futurelab_ai';
if (strpos($requestUri, $basePath) === 0) {
    $requestUri = substr($requestUri, strlen($basePath));
}

// Si está vacío, usar /
if (empty($requestUri) || $requestUri === '/') {
    $requestUri = '/login';
}

// Remover trailing slash excepto para root
if ($requestUri !== '/' && substr($requestUri, -1) === '/') {
    $requestUri = rtrim($requestUri, '/');
}

// =======================
// ROUTING
// =======================

try {
    
    // ===== RUTAS DE AUTENTICACIÓN =====
    
    if ($requestUri === '/login' && $requestMethod === 'GET') {
        $controller = new AuthControlador();
        $controller->mostrarLogin();
        exit;
    }
    
    if ($requestUri === '/auth/login' && $requestMethod === 'POST') {
        $controller = new AuthControlador();
        $controller->procesarLogin();
        exit;
    }
    
    if ($requestUri === '/auth/logout') {
        $controller = new AuthControlador();
        $controller->logout();
        exit;
    }
    
    // ===== MURO PÚBLICO =====
    
    if ($requestUri === '/wall') {
        $controller = new WallControlador();
        $controller->mostrarMuro();
        exit;
    }
    
    if ($requestUri === '/api/public/latest') {
        $controller = new WallControlador();
        $controller->obtenerUltimos();
        exit;
    }
    
    // ===== GENERACIÓN DE PARTICIPANTES (ADMIN/OPERATOR) =====
    
    if ($requestUri === '/admin/generate') {
        $controller = new ParticipanteControlador();
        $controller->mostrarFormulario();
        exit;
    }
    
    if ($requestUri === '/api/participants/create' && $requestMethod === 'POST') {
        $controller = new ParticipanteControlador();
        $controller->crear();
        exit;
    }
    
    if ($requestUri === '/api/participants/status') {
        $controller = new ParticipanteControlador();
        $controller->obtenerStatus();
        exit;
    }
    
    // ===== PANEL ADMINISTRATIVO =====
    
    if ($requestUri === '/admin/participants') {
        $controller = new AdminParticipantesControlador();
        $controller->mostrarLista();
        exit;
    }
    
    if ($requestUri === '/api/admin/participants/datatables') {
        $controller = new AdminParticipantesControlador();
        $controller->datatables();
        exit;
    }
    
    if ($requestUri === '/api/admin/participants/show') {
        $controller = new AdminParticipantesControlador();
        $controller->mostrarDetalle();
        exit;
    }
    
    if ($requestUri === '/api/admin/participants/retry' && $requestMethod === 'POST') {
        $controller = new ParticipanteControlador();
        $controller->reintentarProcesamiento();
        exit;
    }
    
    if ($requestUri === '/api/admin/participants/delete' && $requestMethod === 'POST') {
        $controller = new ParticipanteControlador();
        $controller->eliminar();
        exit;
    }
    
    // ===== CONFIGURACIÓN (ADMIN) =====
    
    if ($requestUri === '/admin/config') {
        $controller = new ConfigControlador();
        $controller->mostrarPanel();
        exit;
    }
    
    if ($requestUri === '/api/config/save' && $requestMethod === 'POST') {
        $controller = new ConfigControlador();
        $controller->guardar();
        exit;
    }
    
    if ($requestUri === '/api/config/test-openai' && $requestMethod === 'POST') {
        $controller = new ConfigControlador();
        $controller->testOpenAI();
        exit;
    }
    
    if ($requestUri === '/api/config/test-falai' && $requestMethod === 'POST') {
        $controller = new ConfigControlador();
        $controller->testFalAI();
        exit;
    }
    
    // ===== GESTIÓN DE CARRERAS (ADMIN) =====
    
    if ($requestUri === '/admin/careers') {
        $controller = new CarreraControlador();
        $controller->mostrarPanel();
        exit;
    }
    
    if ($requestUri === '/admin/careers/edit') {
        $controller = new CarreraControlador();
        $controller->mostrarEditar();
        exit;
    }
    
    if ($requestUri === '/api/careers/update' && $requestMethod === 'POST') {
        $controller = new CarreraControlador();
        $controller->actualizar();
        exit;
    }
    
    if ($requestUri === '/api/careers/delete-image' && $requestMethod === 'POST') {
        $controller = new CarreraControlador();
        $controller->eliminarImagen();
        exit;
    }
    
    if ($requestUri === '/api/careers/datatables') {
        $controller = new CarreraControlador();
        $controller->datatables();
        exit;
    }
    
    if ($requestUri === '/api/careers/create' && $requestMethod === 'POST') {
        $controller = new CarreraControlador();
        $controller->crear();
        exit;
    }
    
    if ($requestUri === '/api/careers/delete' && $requestMethod === 'POST') {
        $controller = new CarreraControlador();
        $controller->eliminar();
        exit;
    }
    
    // ===== RUTA PÚBLICA DE PARTICIPANTE (por código) =====
    
    if (preg_match('#^/p/([A-Z0-9]{12})$#', $requestUri, $matches)) {
        // Mostrar página pública del participante
        $publicCode = $matches[1];
        $controller = new ParticipanteControlador();
        $controller->mostrarPublico($publicCode);
        exit;
    }
    
    // ===== 404 - NOT FOUND =====
    
    http_response_code(404);
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>404 - Página No Encontrada</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            body {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                text-align: center;
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            }
            h1 { font-size: 6rem; font-weight: 700; }
            .btn-home {
                background: white;
                color: #667eea;
                padding: 15px 40px;
                border-radius: 50px;
                font-weight: 600;
                text-decoration: none;
                display: inline-block;
                margin-top: 30px;
            }
            .btn-home:hover {
                transform: scale(1.05);
                box-shadow: 0 10px 30px rgba(255, 255, 255, 0.3);
            }
        </style>
    </head>
    <body>
        <div>
            <h1>404</h1>
            <h2>Página No Encontrada</h2>
            <p>La ruta <code><?php echo htmlspecialchars($requestUri); ?></code> no existe.</p>
            <a href="<?php echo BASE_URL; ?>/login" class="btn-home">
                <i class="bi bi-house-fill"></i> Ir al Inicio
            </a>
        </div>
    </body>
    </html>
    <?php
    exit;
    
} catch (Exception $e) {
    // Manejo de errores global
    http_response_code(500);
    error_log('Error en routing: ' . $e->getMessage());
    
    if (strpos($requestUri, '/api/') === 0) {
        // Error en API - devolver JSON
        header('Content-Type: application/json');
        echo json_encode([
            'ok' => false,
            'error' => 'Error interno del servidor'
        ]);
    } else {
        // Error en página - mostrar HTML
        echo '<!DOCTYPE html>
        <html>
        <head><title>Error 500</title></head>
        <body style="font-family: sans-serif; text-align: center; padding: 50px;">
            <h1>Error 500</h1>
            <p>Ha ocurrido un error interno. Por favor, intenta nuevamente.</p>
        </body>
        </html>';
    }
    exit;
}

<?php
/**
 * Helpers de Autenticación y Autorización
 * Sistema multi-rol para FutureLab AI
 */

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
    session_name(SESSION_NAME);
    session_start();
}

/**
 * Verifica si el usuario está autenticado
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Obtiene los datos del usuario actual
 * @return array|null Datos del usuario o null si no está autenticado
 */
function currentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'] ?? null,
        'username' => $_SESSION['username'] ?? null,
        'roles' => $_SESSION['user_roles'] ?? []
    ];
}

/**
 * Verifica si el usuario tiene un rol específico
 * @param string $roleName Nombre del rol a verificar
 * @return bool
 */
function userHasRole($roleName) {
    if (!isLoggedIn()) {
        return false;
    }
    
    $roles = $_SESSION['user_roles'] ?? [];
    return in_array($roleName, $roles);
}

/**
 * Verifica si el usuario tiene al menos uno de los roles especificados
 * @param array $roleNames Array de nombres de roles
 * @return bool
 */
function userHasAnyRole($roleNames) {
    if (!isLoggedIn()) {
        return false;
    }
    
    $userRoles = $_SESSION['user_roles'] ?? [];
    foreach ($roleNames as $role) {
        if (in_array($role, $userRoles)) {
            return true;
        }
    }
    
    return false;
}

/**
 * Requiere que el usuario esté autenticado
 * Redirige a login si no lo está
 */
function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? '/';
        header('Location: ' . BASE_URL . '/login');
        exit;
    }
}

/**
 * Requiere que el usuario tenga un rol específico
 * @param string|array $roles Rol o array de roles permitidos
 * @param bool $redirectToLogin Si debe redirigir a login o mostrar error 403
 */
function requireRole($roles, $redirectToLogin = false) {
    requireLogin();
    
    if (!is_array($roles)) {
        $roles = [$roles];
    }
    
    if (!userHasAnyRole($roles)) {
        if ($redirectToLogin) {
            header('Location: ' . BASE_URL . '/login?error=forbidden');
            exit;
        } else {
            http_response_code(403);
            require __DIR__ . '/../vista/error_403.php';
            exit;
        }
    }
}

/**
 * Inicia sesión de usuario
 * @param array $userData Datos del usuario (debe incluir id, username y roles)
 */
function loginUser($userData) {
    $_SESSION['user_id'] = $userData['id'];
    $_SESSION['username'] = $userData['username'];
    $_SESSION['user_roles'] = $userData['roles'] ?? [];
    $_SESSION['login_time'] = time();
    
    // Regenerar ID de sesión por seguridad
    session_regenerate_id(true);
}

/**
 * Cierra la sesión del usuario
 */
function logoutUser() {
    $_SESSION = [];
    
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    session_destroy();
}

/**
 * Verifica si el usuario es administrador
 * @return bool
 */
function isAdmin() {
    return userHasRole('admin');
}

/**
 * Verifica si el usuario es operador (puede generar participantes)
 * @return bool
 */
function isOperator() {
    return userHasAnyRole(['admin', 'operator']);
}

/**
 * Genera un token CSRF
 * @return string
 */
function generateCsrfToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verifica un token CSRF
 * @param string $token Token a verificar
 * @return bool
 */
function verifyCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

<?php
/**
 * TEST DE ACCESO RÁPIDO
 * Ejecuta este archivo para verificar que todo funciona
 * http://localhost/futurelab-ai/test.php
 */

echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Test de Acceso - FutureLab AI</title>
    <style>
        body { font-family: Arial; padding: 40px; background: #f5f5f5; }
        .test { background: white; padding: 20px; margin: 10px 0; border-radius: 8px; }
        .ok { color: green; }
        .error { color: red; }
        .warning { color: orange; }
        h1 { color: #667eea; }
        .code { background: #f0f0f0; padding: 10px; border-radius: 4px; margin: 10px 0; }
    </style>
</head>
<body>
    <h1>🔍 Test de Acceso - FutureLab AI</h1>";

// Test 1: Conexión a base de datos
echo "<div class='test'>";
echo "<h2>1. Conexión a Base de Datos</h2>";
try {
    require_once __DIR__ . '/config/config.php';
    require_once __DIR__ . '/modelo/conexion.php';
    
    $conn = new Conexion();
    $db = $conn->conectar();
    
    if ($db) {
        echo "<p class='ok'>✓ Conexión exitosa a la base de datos</p>";
        echo "<p>Base de datos: <strong>" . DB_SCHEMA . "</strong></p>";
    } else {
        echo "<p class='error'>✗ No se pudo conectar a la base de datos</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>✗ Error: " . $e->getMessage() . "</p>";
}
echo "</div>";

// Test 2: Verificar usuario admin
echo "<div class='test'>";
echo "<h2>2. Verificación de Usuario Admin</h2>";
try {
    $stmt = $db->query("SELECT id, username, is_active FROM users WHERE username = 'admin'");
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo "<p class='ok'>✓ Usuario admin existe</p>";
        echo "<p>ID: <strong>{$user['id']}</strong></p>";
        echo "<p>Username: <strong>{$user['username']}</strong></p>";
        echo "<p>Activo: <strong>" . ($user['is_active'] ? 'Sí' : 'No') . "</strong></p>";
        
        // Verificar password
        $stmt2 = $db->query("SELECT password_hash FROM users WHERE username = 'admin'");
        $hash = $stmt2->fetchColumn();
        
        if (password_verify('secret123', $hash)) {
            echo "<p class='ok'>✓ Password 'secret123' es correcto</p>";
        } else {
            echo "<p class='warning'>⚠ Password 'secret123' NO coincide</p>";
            echo "<p>Ejecuta: <code>mysql -u root -p futurelab_ai < update_admin_password.sql</code></p>";
        }
    } else {
        echo "<p class='error'>✗ Usuario admin no existe</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>✗ Error: " . $e->getMessage() . "</p>";
}
echo "</div>";

// Test 3: Verificar carpetas de storage
echo "<div class='test'>";
echo "<h2>3. Carpetas de Storage</h2>";
$folders = [
    'storage',
    'storage/uploads',
    'storage/results',
    'storage/qr'
];

foreach ($folders as $folder) {
    $path = __DIR__ . '/' . $folder;
    if (file_exists($path) && is_dir($path)) {
        $writable = is_writable($path);
        if ($writable) {
            echo "<p class='ok'>✓ /$folder existe y es escribible</p>";
        } else {
            echo "<p class='warning'>⚠ /$folder existe pero NO es escribible</p>";
            echo "<p>Ejecuta: <code>chmod -R 777 storage/</code></p>";
        }
    } else {
        echo "<p class='error'>✗ /$folder NO existe</p>";
        echo "<p>Ejecuta: <code>mkdir -p $folder</code></p>";
    }
}
echo "</div>";

// Test 4: Verificar tablas
echo "<div class='test'>";
echo "<h2>4. Verificación de Tablas</h2>";
try {
    $tables = ['users', 'roles', 'user_roles', 'careers', 'participants', 'audit_log'];
    $allOk = true;
    
    foreach ($tables as $table) {
        $stmt = $db->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "<p class='ok'>✓ Tabla '$table' existe</p>";
        } else {
            echo "<p class='error'>✗ Tabla '$table' NO existe</p>";
            $allOk = false;
        }
    }
    
    if (!$allOk) {
        echo "<p class='warning'>⚠ Importa la base de datos:</p>";
        echo "<div class='code'>mysql -u root -p < futurelab_ai.sql</div>";
    }
} catch (Exception $e) {
    echo "<p class='error'>✗ Error: " . $e->getMessage() . "</p>";
}
echo "</div>";

// Test 5: Verificar carreras
echo "<div class='test'>";
echo "<h2>5. Carreras Disponibles</h2>";
try {
    $stmt = $db->query("SELECT COUNT(*) FROM careers WHERE is_active = 1");
    $count = $stmt->fetchColumn();
    
    if ($count > 0) {
        echo "<p class='ok'>✓ Hay $count carreras activas</p>";
        
        $stmt2 = $db->query("SELECT name FROM careers WHERE is_active = 1 ORDER BY sort_order LIMIT 5");
        $careers = $stmt2->fetchAll(PDO::FETCH_COLUMN);
        echo "<p>Ejemplos: " . implode(', ', $careers) . "...</p>";
    } else {
        echo "<p class='warning'>⚠ No hay carreras registradas</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>✗ Error: " . $e->getMessage() . "</p>";
}
echo "</div>";

// Instrucciones finales
echo "<div class='test'>";
echo "<h2>✅ Siguiente Paso</h2>";
echo "<p>Si todos los tests están en verde, puedes acceder a:</p>";
echo "<div class='code'>";
echo "<a href='http://localhost/futurelab-ai/' target='_blank' style='color:#667eea; font-weight:bold; font-size:18px;'>
        http://localhost/futurelab-ai/
      </a>";
echo "</div>";
echo "<p><strong>Credenciales:</strong></p>";
echo "<ul>";
echo "<li>Usuario: <strong>admin</strong></li>";
echo "<li>Password: <strong>secret123</strong></li>";
echo "</ul>";
echo "</div>";

echo "</body></html>";
?>

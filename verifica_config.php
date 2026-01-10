<?php
/**
 * VERIFICAR CONFIG - Test Rápido
 * http://localhost/futurelab-ai/verifica_config.php
 */

echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Verificar Configuración</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        .test { background: white; padding: 15px; margin: 10px 0; border-radius: 5px; }
        .ok { color: green; }
        .error { color: red; }
        h1 { color: #667eea; }
    </style>
</head>
<body>
    <h1>🔍 Verificación de Configuración</h1>";

// Test 1: Verificar sesión
echo "<div class='test'>";
echo "<h3>1. Sesión</h3>";
session_start();
if (isset($_SESSION['user_id'])) {
    echo "<p class='ok'>✓ Sesión activa: Usuario ID " . $_SESSION['user_id'] . "</p>";
} else {
    echo "<p class='error'>✗ No hay sesión activa. <a href='http://localhost/futurelab-ai/'>Hacer login</a></p>";
}
echo "</div>";

// Test 2: Verificar tabla system_config
echo "<div class='test'>";
echo "<h3>2. Tabla system_config</h3>";
try {
    require_once __DIR__ . '/config/config.php';
    require_once __DIR__ . '/modelo/conexion.php';
    
    $conn = new Conexion();
    $db = $conn->conectar();
    
    $stmt = $db->query("SHOW TABLES LIKE 'system_config'");
    if ($stmt->rowCount() > 0) {
        echo "<p class='ok'>✓ Tabla system_config existe</p>";
        
        // Contar registros
        $stmt2 = $db->query("SELECT COUNT(*) FROM system_config");
        $count = $stmt2->fetchColumn();
        echo "<p class='ok'>✓ Tiene $count configuraciones</p>";
        
        // Mostrar configs
        $stmt3 = $db->query("SELECT config_key, config_value FROM system_config");
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Key</th><th>Value</th></tr>";
        while ($row = $stmt3->fetch(PDO::FETCH_ASSOC)) {
            $value = $row['config_value'];
            if ($row['config_key'] === 'gemini_api_key' && !empty($value)) {
                $value = substr($value, 0, 10) . '...' . substr($value, -5);
            }
            echo "<tr><td>{$row['config_key']}</td><td>$value</td></tr>";
        }
        echo "</table>";
        
    } else {
        echo "<p class='error'>✗ Tabla system_config NO existe</p>";
        echo "<p>Ejecuta: <code>mysql -u root -p futurelab_ai < system_config_table.sql</code></p>";
    }
    
} catch (Exception $e) {
    echo "<p class='error'>✗ Error: " . $e->getMessage() . "</p>";
}
echo "</div>";

// Test 3: Verificar ConfigModel
echo "<div class='test'>";
echo "<h3>3. ConfigModel</h3>";
try {
    require_once __DIR__ . '/modelo/ConfigModel.php';
    
    $configModel = new ConfigModel();
    echo "<p class='ok'>✓ ConfigModel cargado correctamente</p>";
    
    $apiKey = $configModel->get('gemini_api_key');
    if ($apiKey !== null) {
        if (empty($apiKey)) {
            echo "<p style='color:orange;'>⚠ API Key está vacía (configurala en el panel)</p>";
        } else {
            echo "<p class='ok'>✓ API Key está configurada</p>";
        }
    } else {
        echo "<p class='error'>✗ No se pudo obtener API Key</p>";
    }
    
} catch (Exception $e) {
    echo "<p class='error'>✗ Error: " . $e->getMessage() . "</p>";
}
echo "</div>";

// Test 4: Verificar ruta de configuración
echo "<div class='test'>";
echo "<h3>4. Ruta de Configuración</h3>";
echo "<p><strong>URL del panel:</strong></p>";
echo "<p><a href='http://localhost/futurelab-ai/admin/config' target='_blank'>http://localhost/futurelab-ai/admin/config</a></p>";
echo "<p class='ok'>✓ Click en el enlace para acceder</p>";
echo "</div>";

echo "</body></html>";
?>

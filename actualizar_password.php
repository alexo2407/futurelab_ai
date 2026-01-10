<?php
/**
 * ACTUALIZAR PASSWORD DEL ADMIN
 * Abre este archivo en tu navegador: http://localhost/futurelab-ai/actualizar_password.php
 */

// Configuración
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/modelo/conexion.php';

echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Actualizar Password Admin</title>
    <style>
        body { 
            font-family: Arial; 
            padding: 40px; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .container { 
            max-width: 600px; 
            margin: 0 auto; 
            background: white; 
            color: #333;
            padding: 30px; 
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        .ok { color: green; font-weight: bold; font-size: 18px; }
        .error { color: red; font-weight: bold; }
        h1 { color: #667eea; text-align: center; }
        .code { 
            background: #f0f0f0; 
            padding: 15px; 
            border-radius: 8px; 
            margin: 15px 0;
            font-family: monospace;
        }
        .btn {
            background: #667eea;
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none;
            display: inline-block;
            margin-top: 20px;
        }
        .btn:hover { background: #5568d3; }
    </style>
</head>
<body>
    <div class='container'>";

try {
    // Conectar a la base de datos
    $conn = new Conexion();
    $db = $conn->conectar();
    
    echo "<h1>🔑 Actualización de Password</h1>";
    
    // Verificar si el usuario admin existe
    $stmt = $db->prepare("SELECT id, username FROM users WHERE username = 'admin'");
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        echo "<p class='error'>❌ El usuario 'admin' no existe en la base de datos.</p>";
        echo "<p>Primero importa la base de datos:</p>";
        echo "<div class='code'>mysql -u root -p < futurelab_ai.sql</div>";
        exit;
    }
    
    echo "<p>✓ Usuario encontrado: <strong>{$user['username']}</strong> (ID: {$user['id']})</p>";
    
    // Generar nuevo hash para 'secret123'
    $newPassword = 'secret123';
    $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
    
    echo "<p>✓ Nuevo password generado: <strong>$newPassword</strong></p>";
    echo "<p style='font-size:12px; color:#666;'>Hash: $newHash</p>";
    
    // Actualizar password
    $stmt = $db->prepare("UPDATE users SET password_hash = :hash WHERE username = 'admin'");
    $result = $stmt->execute(['hash' => $newHash]);
    
    if ($result) {
        echo "<p class='ok'>✅ PASSWORD ACTUALIZADO EXITOSAMENTE</p>";
        
        // Verificar que funciona
        $stmt = $db->prepare("SELECT password_hash FROM users WHERE username = 'admin'");
        $stmt->execute();
        $currentHash = $stmt->fetchColumn();
        
        if (password_verify($newPassword, $currentHash)) {
            echo "<p class='ok'>✅ VERIFICACIÓN EXITOSA - El password funciona correctamente</p>";
            
            echo "<hr>";
            echo "<h2>🎉 ¡Todo Listo!</h2>";
            echo "<p><strong>Ahora puedes acceder con:</strong></p>";
            echo "<div class='code'>";
            echo "URL: <a href='http://localhost/futurelab-ai/' target='_blank'>http://localhost/futurelab-ai/</a><br>";
            echo "Usuario: <strong>admin</strong><br>";
            echo "Password: <strong>secret123</strong>";
            echo "</div>";
            
            echo "<a href='http://localhost/futurelab-ai/' class='btn'>🚀 IR AL LOGIN</a>";
            
        } else {
            echo "<p class='error'>❌ Error en la verificación. El password no coincide.</p>";
        }
        
    } else {
        echo "<p class='error'>❌ Error al actualizar el password.</p>";
        echo "<p>Error: " . implode(', ', $stmt->errorInfo()) . "</p>";
    }
    
} catch (PDOException $e) {
    echo "<p class='error'>❌ Error de conexión a la base de datos:</p>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<p><strong>Verifica que:</strong></p>";
    echo "<ul>";
    echo "<li>XAMPP esté corriendo (Apache + MySQL)</li>";
    echo "<li>La base de datos 'futurelab_ai' exista</li>";
    echo "<li>Las credenciales en config/config.php sean correctas</li>";
    echo "</ul>";
} catch (Exception $e) {
    echo "<p class='error'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "    </div>
</body>
</html>";
?>

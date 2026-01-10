<?php
/**
 * DEBUG - Verificar Imagen de Carrera
 * http://localhost/futurelab-ai/debug_career_image.php?id=X
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/modelo/conexion.php';
require_once __DIR__ . '/modelo/CarreraModel.php';

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    die("Especifica un ID de carrera: ?id=X");
}

$carreraModel = new CarreraModel();
$carrera = $carreraModel->obtenerPorId($id);

echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Debug Carrera</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        .debug { background: white; padding: 20px; margin: 10px 0; border-radius: 8px; }
        h1 { color: #667eea; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 5px; overflow-x: auto; }
        img { max-width: 400px; border: 2px solid #ddd; margin: 10px 0; }
        .ok { color: green; }
        .error { color: red; }
    </style>
</head>
<body>
    <h1>🔍 Debug Imagen de Carrera</h1>";

if (!$carrera) {
    echo "<div class='debug error'>❌ Carrera no encontrada</div>";
    exit;
}

echo "<div class='debug'>";
echo "<h2>Información de la Carrera</h2>";
echo "<strong>ID:</strong> " . $carrera['id'] . "<br>";
echo "<strong>Nombre:</strong> " . htmlspecialchars($carrera['name']) . "<br>";
echo "<strong>Categoría:</strong> " . htmlspecialchars($carrera['category']) . "<br>";
echo "</div>";

echo "<div class='debug'>";
echo "<h2>Datos de la Imagen</h2>";
echo "<pre>";
print_r([
    'reference_image_path' => $carrera['reference_image_path'] ?? 'NULL',
    'reference_image_url' => $carrera['reference_image_url'] ?? 'NULL'
]);
echo "</pre>";
echo "</div>";

// Verificar imagen local
if (!empty($carrera['reference_image_path'])) {
    echo "<div class='debug'>";
    echo "<h2>Verificación de Imagen Local</h2>";
    
    $imagePath = $carrera['reference_image_path'];
    echo "<strong>Ruta en BD:</strong> <code>$imagePath</code><br>";
    
    // Construir ruta física
    $physicalPath = STORAGE_PATH . str_replace('/storage', '', $imagePath);
    echo "<strong>Ruta física:</strong> <code>$physicalPath</code><br>";
    
    if (file_exists($physicalPath)) {
        echo "<p class='ok'>✅ Archivo existe en el servidor</p>";
        
        $fileSize = filesize($physicalPath);
        echo "<strong>Tamaño:</strong> " . number_format($fileSize / 1024, 2) . " KB<br>";
        
        // Mostrar imagen
        $imageUrl = STORAGE_URL . str_replace('/storage', '', $imagePath);
        echo "<strong>URL pública:</strong> <code>$imageUrl</code><br>";
        
        echo "<h3>Preview:</h3>";
        echo "<img src='$imageUrl' alt='Imagen de referencia'>";
        
        // Método alternativo
        echo "<h3>Método alternativo (BASE_URL directo):</h3>";
        $altUrl = BASE_URL . $imagePath;
        echo "<strong>URL alternativa:</strong> <code>$altUrl</code><br>";
        echo "<img src='$altUrl' alt='Imagen alternativa'>";
        
    } else {
        echo "<p class='error'>❌ Archivo NO existe en el servidor</p>";
        echo "<p>Verifica que el archivo se haya subido correctamente</p>";
    }
    echo "</div>";
}

// Verificar imagen URL
if (!empty($carrera['reference_image_url'])) {
    echo "<div class='debug'>";
    echo "<h2>Imagen desde URL</h2>";
    $imageUrl = $carrera['reference_image_url'];
    echo "<strong>URL:</strong> <code>$imageUrl</code><br>";
    echo "<h3>Preview:</h3>";
    echo "<img src='$imageUrl' alt='Imagen desde URL'>";
    echo "</div>";
}

// Verificar permisos del directorio
echo "<div class='debug'>";
echo "<h2>Verificación de Directorio de Referencias</h2>";
$referencesDir = STORAGE_PATH . '/references';
echo "<strong>Directorio:</strong> <code>$referencesDir</code><br>";

if (!file_exists($referencesDir)) {
    echo "<p class='error'>❌ El directorio NO existe</p>";
    echo "<p>Crear con: <code>mkdir -p $referencesDir && chmod 777 $referencesDir</code></p>";
} else {
    echo "<p class='ok'>✅ El directorio existe</p>";
    
    if (is_writable($referencesDir)) {
        echo "<p class='ok'>✅ El directorio tiene permisos de escritura</p>";
    } else {
        echo "<p class='error'>❌ El directorio NO tiene permisos de escritura</p>";
        echo "<p>Ejecuta: <code>chmod 777 $referencesDir</code></p>";
    }
    
    // Listar archivos
    $files = scandir($referencesDir);
    $imageFiles = array_filter($files, function($f) use ($referencesDir) {
        return is_file($referencesDir . '/' . $f);
    });
    
    echo "<strong>Archivos en el directorio:</strong><br>";
    if (count($imageFiles) > 0) {
        echo "<ul>";
        foreach ($imageFiles as $file) {
            $fileSize = filesize($referencesDir . '/' . $file);
            echo "<li>$file (" . number_format($fileSize / 1024, 2) . " KB)</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>No hay archivos en el directorio</p>";
    }
}
echo "</div>";

echo "</body></html>";
?>

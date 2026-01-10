<?php
/**
 * DEBUG - Endpoint de Participantes
 * Abre: http://localhost/futurelab-ai/debug_participante.php
 * Muestra los datos que está recibiendo el servidor
 */

session_start();

echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Debug - Crear Participante</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #f5f5f5; }
        .section { background: white; padding: 15px; margin: 10px 0; border-radius: 5px; }
        h3 { color: #667eea; }
        pre { background: #f0f0f0; padding: 10px; overflow: auto; }
    </style>
</head>
<body>
    <h1>🐛 Debug - Datos de la Petición</h1>";

echo "<div class='section'>";
echo "<h3>Sesión Actual</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";
echo "</div>";

echo "<div class='section'>";
echo "<h3>POST Data</h3>";
echo "<pre>";
print_r($_POST);
echo "</pre>";
echo "</div>";

echo "<div class='section'>";
echo "<h3>FILES Data</h3>";
echo "<pre>";
print_r($_FILES);
echo "</pre>";
echo "</div>";

echo "<div class='section'>";
echo "<h3>REQUEST_METHOD</h3>";
echo "<pre>" . $_SERVER['REQUEST_METHOD'] . "</pre>";
echo "</div>";

echo "<div class='section'>";
echo "<h3>REQUEST_URI</h3>";
echo "<pre>" . $_SERVER['REQUEST_URI'] . "</pre>";
echo "</div>";

// Test de validación
echo "<div class='section'>";
echo "<h3>Validaciones</h3>";

$errors = [];

if (empty($_POST['first_name'])) {
    $errors[] = "❌ first_name está vacío";
} else {
    echo "✅ first_name: " . htmlspecialchars($_POST['first_name']) . "<br>";
}

if (empty($_POST['last_name'])) {
    $errors[] = "❌ last_name está vacío";
} else {
    echo "✅ last_name: " . htmlspecialchars($_POST['last_name']) . "<br>";
}

if (empty($_POST['career_id'])) {
    $errors[] = "❌ career_id está vacío";
} else {
    echo "✅ career_id: " . htmlspecialchars($_POST['career_id']) . "<br>";
}

if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
    $errors[] = "❌ Foto no cargada correctamente. Error code: " . ($_FILES['photo']['error'] ?? 'N/A');
} else {
    echo "✅ Foto cargada: " . $_FILES['photo']['name'] . " (" . $_FILES['photo']['size'] . " bytes)<br>";
}

if (!empty($errors)) {
    echo "<h4 style='color:red;'>Errores encontrados:</h4>";
    foreach ($errors as $error) {
        echo "<p>$error</p>";
    }
}

echo "</div>";

echo "</body></html>";
?>

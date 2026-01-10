<?php
require_once __DIR__ . '/../../config/auth.php';
requireRole('admin');
$user = currentUser();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar

 Carrera - FutureLab AI</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        body {
            background: #f5f7fa;
        }
        
        .header {
            background: var(--primary-gradient);
            color: white;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .edit-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin-bottom: 20px;
        }
        
        .reference-preview {
            max-width: 300px;
            max-height: 300px;
            border-radius: 10px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="container">
            <a href="<?php echo BASE_URL; ?>/admin/careers" class="btn btn-light btn-sm mb-2">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
            <h1><i class="bi bi-pencil-fill me-2"></i>Editar Carrera</h1>
            <p class="mb-0"><?php echo htmlspecialchars($carrera['name']); ?></p>
        </div>
    </div>
    
    <div class="container">
        <div class="edit-card">
            <form id="form-edit-career" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?php echo $carrera['id']; ?>">
                
                <h4 class="mb-3"><i class="bi bi-robot text-primary"></i> Configuración de IA</h4>
                
                <!-- Prompt personalizado -->
                <div class="mb-4">
                    <label for="ai_prompt" class="form-label">
                        <i class="bi bi-chat-text"></i> Prompt Completo y Listo
                    </label>
                    <textarea 
                        class="form-control" 
                        id="ai_prompt" 
                        name="ai_prompt" 
                        rows="6"
                        placeholder="Ejemplo: Transforma esta foto en una imagen profesional de {nombre} como ingeniero de software. El sujeto debe aparecer trabajando en una oficina moderna..."><?php echo htmlspecialchars($carrera['ai_prompt'] ?? ''); ?></textarea>
                    <small class="text-muted d-block mt-2">
                        <strong>Instrucciones para GPT-Image-1:</strong><br>
                        • GPT-Image-1 usa la foto del participante como base y la TRANSFORMA.<br>
                        • Describe cómo debe verse la persona transformada (vestimenta, acción, fondo).<br>
                        • Usa variables: <code>{nombre}</code> y <code>{carrera}</code>.<br>
                        • El modelo preservará la identidad facial del participante.
                    </small>
                    <div class="alert alert-info mt-2">
                        <strong>💡 Ejemplo de prompt efectivo:</strong><br>
                        <code>
                        "Transforma esta foto en una imagen cinemática de {nombre} como astronauta. 
                        El sujeto debe llevar un traje espacial blanco detallado con la bandera de México. 
                        Fondo de paisaje marciano realista. Iluminación dramática, alta resolución."
                        </code>
                    </div>
                </div>
                
                <!-- Estado activo -->
                <div class="mb-4 form-check">
                    <input type="checkbox" 
                           class="form-check-input" 
                           id="is_active" 
                           name="is_active"
                           <?php echo $carrera['is_active'] ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="is_active">
                        Carrera activa (visible en el formulario de generación)
                    </label>
                </div>
                
                <!-- Botones -->
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="bi bi-save"></i> Guardar Cambios
                    </button>
                    <a href="<?php echo BASE_URL; ?>/admin/careers" class="btn btn-secondary btn-lg">
                        <i class="bi bi-x-circle"></i> Cancelar
                    </a>
                </div>
            </form>
        </div>
        
        <!-- Ayuda -->
        <div class="edit-card">
            <h5><i class="bi bi-lightbulb text-warning"></i> Consejos</h5>
            <ul>
                <li><strong>Descripción clara:</strong> Describe detalladamente el rol, vestimenta y entorno deseado.</li>
                <li><strong>Preservación de identidad:</strong> El modelo intentará mantener los rasgos faciales de la foto original.</li>
                <li><strong>Ejemplos de prompts efectivos:</strong>
                    <ul>
                        <li>Ingenería: "Retrato cyberpunk de {nombre} construyendo un robot futurista..."</li>
                        <li>Diseño: "{nombre} pintando un mural colorido en un estudio artístico luminoso..."</li>
                        <li>Medicina: "{nombre} como cirujano experto realizando una operación compleja..."</li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Guardar cambios
        document.getElementById('form-edit-career').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Guardando...';
            
            try {
                const response = await fetch('<?php echo BASE_URL; ?>/api/careers/update', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.ok) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Guardado!',
                        text: 'Cambios guardados exitosamente',
                        confirmButtonColor: '#667eea',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = '<?php echo BASE_URL; ?>/admin/careers';
                    });
                } else {
                    throw new Error(data.error || 'Error al guardar');
                }
                
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.message,
                    confirmButtonColor: '#667eea'
                });
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        });
    </script>
</body>
</html>

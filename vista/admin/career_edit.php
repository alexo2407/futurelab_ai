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
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/vista/css/estilos.css">
    
    <style>
        .reference-preview {
            max-width: 300px;
            max-height: 300px;
            border-radius: 10px;
            margin-top: 10px;
        }
        
        .reference-image-container {
            position: relative;
            display: inline-block;
        }
        
        .delete-image-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 10;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col">
                    <h1><i class="bi bi-mortarboard-fill me-2"></i>Gestión de Carreras</h1>
                </div>
                <div class="col-auto">
                    <a href="<?php echo BASE_URL; ?>/admin/generate" class="btn btn-light me-2">
                        <i class="bi bi-camera"></i> Generar
                    </a>
                    <a href="<?php echo BASE_URL; ?>/admin/participants" class="btn btn-light me-2">
                        <i class="bi bi-list-ul"></i> Participantes
                    </a>
                    <a href="<?php echo BASE_URL; ?>/admin/careers" class="btn btn-light me-2">
                        <i class="bi bi-mortarboard"></i> Carreras
                    </a>
                    <a href="<?php echo BASE_URL; ?>/wall" class="btn btn-outline-light me-2" target="_blank">
                        <i class="bi bi-display"></i> Muro
                    </a>
                    <a href="<?php echo BASE_URL; ?>/admin/config" class="btn btn-outline-light me-2">
                        <i class="bi bi-gear"></i> Config
                    </a>
                    <span class="text-white me-2">
                        <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($user['username']); ?>
                    </span>
                    <a href="<?php echo BASE_URL; ?>/auth/logout" class="btn btn-outline-light">
                        <i class="bi bi-box-arrow-right"></i> Salir
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="container">
        <div class="mb-4">
             <a href="<?php echo BASE_URL; ?>/admin/careers" class="btn btn-outline-secondary btn-sm mb-2">
                <i class="bi bi-arrow-left"></i> Volver a la lista
            </a>
            <h2><i class="bi bi-pencil-fill me-2"></i>Editar Carrera</h2>
            <h4 class="text-muted"><?php echo htmlspecialchars($carrera['name']); ?></h4>
        </div>

        <div class="edit-card card fade-in">
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
                        placeholder="Ejemplo: Toma la primera imagen como referencia de estilo. Reemplaza al sujeto de esa imagen con {nombre}. Mantén el mismo estilo, ambiente y composición."><?php echo htmlspecialchars($carrera['ai_prompt'] ?? ''); ?></textarea>
                    <small class="text-muted d-block mt-2">
                        <strong>📸 Cómo funciona con fal.ai + Imagen de Referencia:</strong><br>
                        • <strong>Imagen 1:</strong> Foto del participante (tomada en el evento)<br>
                        • <strong>Imagen 2:</strong> Tu arte/referencia (sube abajo) - el "molde" artístico<br>
                        • <strong>Resultado:</strong> La persona reemplaza al sujeto del arte manteniendo el estilo<br>
                        • Variables disponibles: <code>{nombre}</code> y <code>{carrera}</code>
                    </small>
                    <div class="alert alert-warning mt-2">
                        <strong>🎨 Prompt recomendado para reemplazo de sujeto:</strong><br>
                        <code>
                        "Usa la primera imagen como referencia de estilo. Reemplaza completamente al sujeto 
                        de esa imagen con la segunda imagen de {nombre}. Mantén exactamente el mismo estilo 
                        artístico, iluminación y composición de la referencia. Preserva la identidad facial de {nombre}."
                        </code>
                    </div>
                </div>
                
                <!-- IMAGEN DE REFERENCIA -->
                <hr class="my-4">
                <h4 class="mb-3"><i class="bi bi-image text-success"></i> Imagen de Referencia (Arte Base)</h4>
                
                <div class="mb-4">
                    <label class="form-label">
                        <i class="bi bi-upload"></i> Sube tu arte/imagen de referencia
                    </label>
                    
                    <?php if (!empty($carrera['reference_image_path'])): ?>
                    <div class="mb-3">
                        <div class="reference-image-container">
                            <img src="<?php echo BASE_URL . '/' . $carrera['reference_image_path']; ?>" 
                                 class="reference-preview img-thumbnail" 
                                 alt="Imagen de referencia actual">
                            <button type="button" class="btn btn-danger btn-sm delete-image-btn" onclick="deleteReferenceImage()">
                                <i class="bi bi-trash"></i> Eliminar
                            </button>
                        </div>
                        <div class="mt-2">
                            <small class="text-success"><i class="bi bi-check-circle"></i> Imagen de referencia actual</small>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-secondary">
                        <i class="bi bi-info-circle"></i> No hay imagen de referencia. 
                        El sistema solo transformará la foto del participante según el prompt.
                    </div>
                    <?php endif; ?>
                    
                    <input 
                        type="file" 
                        class="form-control" 
                        id="reference_image" 
                        name="reference_image"
                        accept="image/jpeg,image/png,image/webp">
                    
                    <small class="text-muted d-block mt-2">
                        <strong>🎨 Qué subir:</strong> Tu arte, ilustración, render 3D, foto estilizada - cualquier imagen 
                        que sirva como "molde" artístico. La persona del participante reemplazará al sujeto de esta imagen.<br>
                        <strong>Formato:</strong> JPG, PNG o WebP. Máx 5MB. Recomendado: 1024x1024 o superior.
                    </small>
                    
                    <!-- Preview -->
                    <div id="new-reference-preview" class="mt-3" style="display: none;">
                        <p class="text-success"><i class="bi bi-check-circle"></i> Nueva imagen seleccionada:</p>
                        <img id="preview-img" class="reference-preview img-thumbnail" alt="Preview">
                    </div>
                </div>
                
                <!-- URL alternativa -->
                <div class="mb-4">
                    <label for="reference_image_url" class="form-label">
                        <i class="bi bi-link-45deg"></i> O usa una URL de imagen
                    </label>
                    <input 
                        type="url" 
                        class="form-control" 
                        id="reference_image_url" 
                        name="reference_image_url"
                        value="<?php echo htmlspecialchars($carrera['reference_image_url'] ?? ''); ?>"
                        placeholder="https://ejemplo.com/mi-arte.jpg">
                    <small class="text-muted">
                        Alternativamente, pega aquí la URL directa de una imagen en internet. 
                        Esta tiene prioridad sobre el archivo subido.
                    </small>
                </div>
                
                <hr class="my-4">
                
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
    
    <footer class="text-center py-4 mt-5 text-muted">
        <div class="container">
            <p class="mb-0">Desarrollado por Alberto Calero</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Preview de imagen de referencia
        document.getElementById('reference_image').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    document.getElementById('preview-img').src = event.target.result;
                    document.getElementById('new-reference-preview').style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });
        
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
        
        // Eliminar imagen de referencia
        async function deleteReferenceImage() {
            const result = await Swal.fire({
                title: '¿Eliminar imagen?',
                text: 'Se eliminará la imagen de referencia de esta carrera',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            });
            
            if (!result.isConfirmed) return;
            
            try {
                const response = await fetch('<?php echo BASE_URL; ?>/api/careers/delete-image', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        id: <?php echo $carrera['id']; ?>
                    })
                });
                
                const data = await response.json();
                
                if (data.ok) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Eliminada!',
                        text: 'Imagen eliminada exitosamente',
                        confirmButtonColor: '#667eea',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    throw new Error(data.error || 'Error al eliminar');
                }
                
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.message,
                    confirmButtonColor: '#667eea'
                });
            }
        }
    </script>
</body>
</html>

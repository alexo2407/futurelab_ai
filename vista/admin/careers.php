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
    <title>Gestión de Carreras - FutureLab AI</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/vista/css/estilos.css">
    
    <style>
        .reference-thumb {
            max-width: 60px;
            max-height: 60px;
            border-radius: 6px;
        }
    </style>
</head>
<body>
    <div class="bg-animation"></div>
    <div class="header">
        <div class="container-fluid">
            <div class="row align-items-center">
               <div class="col">
                    <h1><i class="bi bi-mortarboard-fill me-2 text-accent"></i>Gestión de Carreras</h1>
                    <p class="mb-0">Personaliza prompts e imágenes de referencia para Gemini AI</p>
                </div>
                <div class="col-auto">
                    <a href="<?php echo BASE_URL; ?>/admin/generate" class="btn btn-primary me-2">
                        <i class="bi bi-camera"></i> Generar
                    </a>
                    <a href="<?php echo BASE_URL; ?>/admin/participants" class="btn btn-primary me-2">
                        <i class="bi bi-list-ul"></i> Participantes
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
    
    <div class="container" style="max-width: 1400px;">
        <div class="card mb-4">
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>Personalización por carrera:</strong> Define un prompt único y/o imagen de referencia para cada carrera. 
                    El worker usará esta información al generar imágenes con Gemini AI.
                </div>
                
                <div class="mb-3">
                    <button class="btn btn-success" onclick="showCreateModal()">
                        <i class="bi bi-plus-circle"></i> Nueva Carrera
                    </button>
                </div>
                
                <table id="careers-table" class="table table-hover" style="width:100%">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Carrera</th>
                            <th>Categoría</th>
                            <th>Prompt</th>
                            <th>Imagen</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        $(document).ready(function() {
            $('#careers-table').DataTable({
                ajax: {
                    url: '<?php echo BASE_URL; ?>/api/careers/datatables',
                    type: 'GET'
                },
                columns: [
                    { data: 'id', width: '50px' },
                    { 
                        data: 'name',
                        render: function(data, type, row) {
                            return '<strong>' + data + '</strong>';
                        }
                    },
                    { data: 'category' },
                    { 
                        data: 'ai_prompt',
                        render: function(data, type, row) {
                            if (data) {
                                return '<span class="badge bg-success"><i class="bi bi-check-circle"></i> Sí</span>';
                            }
                            return '<span class="badge bg-secondary">No</span>';
                        },
                        width: '80px'
                    },
                    { 
                        data: 'reference_image_path',
                        render: function(data, type, row) {
                            if (data) {
                                return '<img src="<?php echo BASE_URL; ?>/' + data + '" class="reference-thumb">';
                            } else if (row.reference_image_url) {
                                return '<span class="badge bg-primary"><i class="bi bi-link"></i> URL</span>';
                            }
                            return '<span class="badge bg-secondary">No</span>';
                        },
                        orderable: false,
                        width: '100px'
                    },
                    { 
                        data: 'is_active',
                        render: function(data, type, row) {
                            if (data == 1) {
                                return '<span class="badge bg-success">Activa</span>';
                            }
                            return '<span class="badge bg-secondary">Inactiva</span>';
                        },
                        width: '100px'
                    },
                    { 
                        data: null,
                        render: function(data, type, row) {
                            return `
                                <a href="<?php echo BASE_URL; ?>/admin/careers/edit?id=${row.id}" 
                                   class="btn btn-sm btn-primary me-1">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button onclick="deleteCareer(${row.id}, '${row.name}')" 
                                        class="btn btn-sm btn-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                            `;
                        },
                        orderable: false,
                        width: '140px'
                    }
                ],
                order: [[0, 'asc']],
                pageLength: 25,
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                },
                responsive: true
            });
        });
        
        // Mostrar modal de crear carrera
        function showCreateModal() {
            Swal.fire({
                title: 'Nueva Carrera',
                html: `
                    <input id="career-name" class="swal2-input" placeholder="Nombre de la carrera">
                    <input id="career-category" class="swal2-input" placeholder="Categoría">
                `,
                showCancelButton: true,
                confirmButtonText: 'Crear',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#198754',
                preConfirm: () => {
                    const name = document.getElementById('career-name').value;
                    const category = document.getElementById('career-category').value;
                    
                    if (!name) {
                        Swal.showValidationMessage('El nombre es requerido');
                        return false;
                    }
                    
                    return { name, category };
                }
            }).then(async (result) => {
                if (result.isConfirmed) {
                    await createCareer(result.value);
                }
            });
        }
        
        // Crear carrera
        async function createCareer(data) {
            try {
                const response = await fetch('<?php echo BASE_URL; ?>/api/careers/create', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (result.ok) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Creada!',
                        text: 'Carrera creada exitosamente',
                        timer: 1500,
                        showConfirmButton: false
                    });
                    $('#careers-table').DataTable().ajax.reload();
                } else {
                    throw new Error(result.error || 'Error al crear');
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.message
                });
            }
        }
        
        // Eliminar carrera
        async function deleteCareer(id, name) {
            const result = await Swal.fire({
                title: '¿Eliminar carrera?',
                text: `Se eliminará "${name}" permanentemente`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            });
            
            if (!result.isConfirmed) return;
            
            try {
                const response = await fetch('<?php echo BASE_URL; ?>/api/careers/delete', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ id })
                });
                
                const data = await response.json();
                
                if (data.ok) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Eliminada!',
                        text: 'Carrera eliminada exitosamente',
                        timer: 1500,
                        showConfirmButton: false
                    });
                    $('#careers-table').DataTable().ajax.reload();
                } else {
                    throw new Error(data.error || 'Error al eliminar');
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.message
                });
            }
        }
    </script>
</body>
</html>

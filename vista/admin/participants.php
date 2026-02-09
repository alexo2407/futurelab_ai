<?php
require_once __DIR__ . '/../../config/auth.php';
requireRole(['admin', 'operator']);
$user = currentUser();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Participantes - FutureLab AI</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">
    
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/vista/css/estilos.css">
    
    <style>
        body {
            color: var(--text-primary);
        }
        
        .header {
            background: rgba(0, 27, 103, 0.8);
            backdrop-filter: blur(10px);
            color: white;
            padding: 20px;
            margin-bottom: 30px;
            border-bottom: 1px solid var(--glass-border);
        }
        
        .main-container {
            padding: 0 20px 40px;
            max-width: 1400px;
 margin: 0 auto;
        }
        
        /* Handled by global .glass-card class */
        
        .table {
            color: var(--text-secondary);
        }
        
        .table th {
            background-color: rgba(0, 27, 103, 0.5);
            color: var(--accent-color);
            border-bottom: 2px solid var(--secondary-color);
            border-top: none;
        }
        
        .table td {
            border-bottom: 1px solid var(--glass-border);
            vertical-align: middle;
        }
        
        .dataTables_wrapper .dataTables_length, 
        .dataTables_wrapper .dataTables_filter, 
        .dataTables_wrapper .dataTables_info, 
        .dataTables_wrapper .dataTables_paginate {
            color: var(--text-secondary) !important;
            margin-top: 1rem;
        }
        
        .dataTables_wrapper .dataTables_length select,
        .dataTables_wrapper .dataTables_filter input {
            background-color: rgba(0, 10, 31, 0.6);
            border: 1px solid var(--secondary-color);
            color: white;
        }

        .page-link {
            background-color: rgba(0, 10, 31, 0.6);
            border-color: var(--secondary-color);
            color: var(--text-primary);
        }
        
        .page-item.active .page-link {
            background-color: var(--primary-color);
            border-color: var(--accent-color);
            color: var(--accent-color);
        }

        .page-item.disabled .page-link {
             background-color: rgba(255, 255, 255, 0.05);
             border-color: var(--glass-border);
             color: rgba(255, 255, 255, 0.3);
        }
    </style>
</head>
<body>
    <div class="bg-animation"></div>
    <div class="header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col">
                    <h1><i class="bi bi-people-fill me-2 text-accent"></i>Lista de Participantes</h1>
                </div>
                <div class="col-auto">
                    <a href="<?php echo BASE_URL; ?>/admin/generate" class="btn btn-primary me-2">
                        <i class="bi bi-plus-circle"></i> Generar Nuevo
                    </a>
                    <a href="<?php echo BASE_URL; ?>/admin/careers" class="btn btn-primary me-2">
                        <i class="bi bi-mortarboard"></i> Carreras
                    </a>
                    <a href="<?php echo BASE_URL; ?>/wall" class="btn btn-outline-light me-2" target="_blank">
                        <i class="bi bi-display"></i> Ver Muro
                    </a>
                    <a href="<?php echo BASE_URL; ?>/admin/config" class="btn btn-outline-light me-2">
                        <i class="bi bi-gear"></i> Configuración
                    </a>
                    <span class="text-white-50 me-2">
                        <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($user['username']); ?>
                    </span>
                    <a href="<?php echo BASE_URL; ?>/auth/logout" class="btn btn-outline-light">
                        <i class="bi bi-box-arrow-right"></i> Salir
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="main-container">
        <div class="card fade-in">
            <div class="card-body">
                <table id="participants-table" class="table table-glass table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Fecha</th>
                            <th>Nombre</th>
                            <th>Teléfono</th>
                            <th>Carrera</th>
                            <th>Estado</th>
                            <th>Resultado</th>
                            <th>QR</th>
                            <th>Creado por</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- DataTables populará esto -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Modal para ver imagen -->
    <div class="modal fade" id="imageModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Imagen Generada</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="modalImage" class="img-fluid" alt="Imagen">
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal para ver QR -->
    <div class="modal fade" id="qrModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-dark text-white border-secondary">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title">Código QR</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="modalQR" class="img-fluid p-2 rounded" style="background: var(--bg-elevated);" alt="QR Code">
                    <p class="mt-3"><strong>Código:</strong> <span id="modalQRCode" class="text-accent"></span></p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal para detalle -->
    <div class="modal fade" id="detailModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detalle del Participante</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="detailContent">
                    <div class="text-center">
                        <div class="spinner-border" role="status"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <footer class="text-center py-4 mt-5 text-white-50">
        <div class="container">
            <p class="mb-0">Desarrollado por Alberto Calero</p>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    
    <!-- DataTables Buttons -->
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Inicializar DataTable
            const table = $('#participants-table').DataTable({
                dom: 'Bfrtip',
                buttons: [
                    {
                        extend: 'excel',
                        text: '<i class="bi bi-file-earmark-excel"></i> Excel',
                        className: 'btn btn-success btn-sm'
                    },
                    {
                        extend: 'csv',
                        text: '<i class="bi bi-file-earmark-spreadsheet"></i> CSV',
                        className: 'btn btn-primary btn-sm'
                    },
                    {
                        extend: 'pdf',
                        text: '<i class="bi bi-file-earmark-pdf"></i> PDF',
                        className: 'btn btn-danger btn-sm'
                    },
                    {
                        extend: 'print',
                        text: '<i class="bi bi-printer"></i> Imprimir',
                        className: 'btn btn-secondary btn-sm'
                    }
                ],
                processing: true,
                serverSide: true,
                ajax: '<?php echo BASE_URL; ?>/api/admin/participants/datatables',
                columns: [
                    { data: 0 },
                    { data: 1 },
                    { data: 2 },
                    { data: 3 }, // Teléfono
                    { data: 4 },
                    { data: 5 },
                    { data: 6, orderable: false },
                    { data: 7, orderable: false },
                    { data: 8 },
                    { data: 9, orderable: false }
                ],
                order: [[0, 'desc']],
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                },
                pageLength: 25
            });
            
            // Auto-refresh cada 30 segundos
            setInterval(() => {
                table.ajax.reload(null, false);
            }, 30000);
        });
        
        // Ver imagen en modal
        function verImagenModal(url) {
            $('#modalImage').attr('src', url);
            new bootstrap.Modal($('#imageModal')).show();
        }
        
        // Ver QR en modal
        function verQRModal(url, code) {
            $('#modalQR').attr('src', url);
            $('#modalQRCode').text(code);
            new bootstrap.Modal($('#qrModal')).show();
        }
        
        // Ver detalle
        async function verDetalle(id) {
            const modal = new bootstrap.Modal($('#detailModal'));
            modal.show();
            
            try {
                const response = await fetch('<?php echo BASE_URL; ?>/api/admin/participants/show?id=' + id);
                const result = await response.json();
                
                if (!result.ok) {
                    throw new Error(result.error);
                }
                
                const data = result.data;
                
                let html = `
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Información General</h6>
                            <p><strong>ID:</strong> ${data.id}</p>
                            <p><strong>Código Público:</strong> ${data.public_code}</p>
                            <p><strong>Nombre:</strong> ${data.first_name} ${data.last_name}</p>
                            <p><strong>Teléfono:</strong> ${data.phone ? data.phone : 'N/A'}</p>
                            <p><strong>Carrera:</strong> ${data.career_name}</p>
                            <p><strong>Estado:</strong> ${data.status}</p>
                            <p><strong>Creado:</strong> ${data.created_at}</p>
                            <p><strong>Creado por:</strong> ${data.created_by_username}</p>
                        </div>
                        <div class="col-md-6">
                            <h6>Archivos</h6>
                `;
                
                if (data.photo_original_url) {
                    html += `<p><strong>Foto Original:</strong><br><img src="${data.photo_original_url}" class="img-thumbnail" style="max-width: 200px;"></p>`;
                }
                
                if (data.result_image_url) {
                    html += `<p><strong>Resultado:</strong><br><img src="${data.result_image_url}" class="img-thumbnail" style="max-width: 200px;"></p>`;
                }
                
                if (data.error_message) {
                    html += `<p><strong>Error:</strong><br><span class="text-danger">${data.error_message}</span></p>`;
                }
                
                html += `</div></div>`;
                
                $('#detailContent').html(html);
                
            } catch (err) {
                $('#detailContent').html(`<div class="alert alert-danger">${err.message}</div>`);
            }
        }
        
        // Reintentar procesamiento
        async function reintentar(id) {
            const result = await Swal.fire({
                title: '¿Reintentar procesamiento?',
                text: '¿Deseas reintentar el procesamiento de este participante?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#667eea',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, reintentar',
                cancelButtonText: 'Cancelar'
            });

            if (!result.isConfirmed) return;
            
            try {
                const formData = new FormData();
                formData.append('id', id);
                
                const response = await fetch('<?php echo BASE_URL; ?>/api/admin/participants/retry', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (!data.ok) {
                    throw new Error(data.error);
                }
                
                Swal.fire({
                    icon: 'success',
                    title: '¡Reintento programado!',
                    text: 'El worker procesará este participante.',
                    confirmButtonColor: '#667eea',
                    timer: 2000
                });
                $('#participants-table').DataTable().ajax.reload();
                
            } catch (err) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error: ' + err.message,
                    confirmButtonColor: '#667eea'
                });
            }
        }
        
        // Eliminar participante
         function eliminarParticipante(id) {
            Swal.fire({
                title: '¿Eliminar Participante?',
                text: 'Esta acción no se puede deshacer',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#667eea',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (!result.isConfirmed) return;
                
                const formData = new FormData();
                formData.append('id', id);
                
                fetch('<?php echo BASE_URL; ?>/api/admin/participants/delete', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.ok) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Eliminado!',
                            text: 'Participante eliminado exitosamente',
                            confirmButtonColor: '#667eea',
                            timer: 2000
                        });
                        $('#participants-table').DataTable().ajax.reload();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.error,
                            confirmButtonColor: '#667eea'
                        });
                    }
                })
                .catch(err => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error de red o servidor: ' + err.message,
                        confirmButtonColor: '#667eea'
                    });
                });
            });
        }
    </script>
</body>
</html>

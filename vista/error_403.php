<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Denegado - FutureLab AI</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <!-- Global CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/vista/css/estilos.css">
    
    <style>
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
            /* Global CSS handles background */
        }
        
        .error-icon {
            font-size: 5rem;
            color: var(--accent-gold);
            margin-bottom: 20px;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.8; transform: scale(1.05); }
            100% { opacity: 1; transform: scale(1); }
        }
        
        .btn-home {
            background: var(--brand-gradient);
            border: 1px solid var(--secondary-color);
            color: white;
            padding: 12px 30px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
            text-decoration: none;
            display: inline-block;
            margin-top: 20px;
        }
        
        .btn-home:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 27, 103, 0.4);
            color: white;
            background: var(--primary-color);
        }
    </style>
</head>
<body>
    <div class="glass-card fade-in text-center p-5" style="width: 100%; max-width: 500px; margin: auto;">
        <div class="error-icon">
            <i class="bi bi-shield-lock-fill"></i>
        </div>
        
        <h1 class="display-5 fw-bold text-white mb-3">Acceso Denegado</h1>
        
        <div class="alert alert-danger border-0 bg-opacity-25 bg-danger text-light mb-4 text-start d-flex align-items-center">
            <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
            <div>
                <strong>403 Forbidden</strong><br>
                No tienes los permisos necesarios para acceder a este recurso.
            </div>
        </div>
        
        <p class="text-white-50 mb-4">
            Si crees que esto es un error, por favor contacta al administrador del sistema o intenta iniciar sesión con una cuenta diferente.
        </p>
        
        <div class="d-grid gap-2 d-sm-flex justify-content-sm-center">
            <a href="<?php echo BASE_URL; ?>/login" class="btn btn-outline-light px-4">
                <i class="bi bi-person-circle me-2"></i> Cambiar Cuenta
            </a>
            <a href="<?php echo BASE_URL; ?>/" class="btn-home">
                <i class="bi bi-house-door-fill me-2"></i> Ir al Inicio
            </a>
        </div>
    </div>
    
    <footer class="fixed-bottom text-center py-3 text-white-50">
        <small>FutureLab AI &copy; <?php echo date('Y'); ?></small>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

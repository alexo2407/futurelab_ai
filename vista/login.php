<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - FutureLab AI</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/vista/css/estilos.css">
    
    <style>
        body {
            /* Global CSS handles background */
        }
        
        /* Handled by global .glass-card class */
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        
        .login-header {
            background: rgba(0, 27, 103, 0.5);
            color: white;
            padding: 40px 30px;
            text-align: center;
            border-bottom: 1px solid var(--glass-border);
        }
        
        .login-header h1 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .login-header p {
            font-size: 0.95rem;
            opacity: 0.9;
            margin: 0;
        }
        
        .login-body {
            padding: 40px 30px;
        }
        
        .form-floating {
            margin-bottom: 20px;
        }
        
        .form-control {
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 15px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.15);
        }
        
        .btn-login {
            background: var(--brand-gradient);
            border: 1px solid var(--secondary-color);
            color: white;
            padding: 15px;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            width: 100%;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 27, 103, 0.4);
            color: var(--accent-color);
            border-color: var(--accent-color);
            background: var(--primary-color);
        }
        
        .alert {
            border-radius: 10px;
            border: none;
            animation: shake 0.5s;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }
        
        .logo-icon {
            font-size: 3rem;
            margin-bottom: 15px;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }
    </style>
</head>
<body>
    <div class="bg-animation"></div>
    <div class="login-container card fade-in" style="max-width: 450px; margin: 0 auto;">
        <div class="login-header">
            <div class="logo-icon text-accent">
                <i class="bi bi-robot"></i>
            </div>
            <h1>FutureLab AI</h1>
            <p class="text-secondary">Sistema de Eventos Interactivos</p>
        </div>
        
        <div class="login-body">
            <?php if (isset($_SESSION['error_login'])): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <?php echo htmlspecialchars($_SESSION['error_login']); ?>
                </div>
                <?php unset($_SESSION['error_login']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <?php echo htmlspecialchars($_SESSION['success_message']); ?>
                </div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>
            
            <form action="<?php echo BASE_URL; ?>/auth/login" method="POST">
                <div class="form-floating">
                    <input type="text" class="form-control" id="username" name="username" 
                           placeholder="Usuario" required autofocus>
                    <label for="username">
                        <i class="bi bi-person-fill me-2"></i>Usuario
                    </label>
                </div>
                
                <div class="form-floating">
                    <input type="password" class="form-control" id="password" name="password" 
                           placeholder="Contraseña" required>
                    <label for="password">
                        <i class="bi bi-lock-fill me-2"></i>Contraseña
                    </label>
                </div>
                
                <button type="submit" class="btn btn-login">
                    <i class="bi bi-box-arrow-in-right me-2"></i>
                    Iniciar Sesión
                </button>
            </form>
            
            <div class="text-center mt-4">
                <small class="text-white-50">
                    <i class="bi bi-info-circle me-1"></i>
                    Usuarios de prueba: admin, operador, viewer
                </small>
            </div>
        </div>
    </div>
    
    <footer class="fixed-bottom text-center py-3 text-white-50">
        <p class="mb-0">Desarrollado por Alberto Calero</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

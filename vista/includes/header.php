<!doctype html>
<html lang="es">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-giJF6kkoqNQ00vy+HMDP7azOuL0xtbfIcaT9wjKHr8RbDVddVHyTfAAsrekwKmP1" crossorigin="anonymous">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <!-- Global CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/vista/css/estilos.css">
    
    <title>FutureLab AI</title>
  </head>
  <body>
    
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#"><i class="bi bi-robot text-accent me-2"></i>FutureLab AI</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0"> 
                
             

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Administración
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <li>
                            <a class="dropdown-item" href="index.php?enlaceBack=articulos">Artículos</a>
                            </li>
                            <li>
                            <a class="dropdown-item" href="index.php?enlaceBack=comentarios">Comentarios</a>
                            </li>                        
                        </ul>
                    </li>

                    

                    <li class="nav-item">
                            <a class="nav-link" href="index.php?enlaceBack=usuarios">Usuarios</a>
                      </li>  
                      
                </ul>

                <ul class="navbar-nav mb-2 mb-lg-0">                       
                        <li class="nav-item">
                            <a class="nav-link" href="../index.php">Inicio</a>
                        </li>
                      
                            <!-- <li class="nav-item">
                                <a class="nav-link" href="index.php?enlace=registrar">Registrarse</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="../acceder.php">Acceder</a>
                            </li> -->
                      
                         
                          <li class="nav-item">
                              <p class="text-white mt-2"><i class="bi bi-person-circle"></i></p>
                          </li>
                          <li class="nav-item">
                                <a class="nav-link" href="index.php?enlaceBack=salir">Salir</a>
                            </li>  
                                
                    </ul> 
                      
            </div>
        </div>
    </nav>  

    <div class="container mt-5 caja">
       
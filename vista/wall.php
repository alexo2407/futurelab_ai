<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Muro Público - FutureLab AI</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/vista/css/estilos.css">
    
    <style>
        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
            overflow: hidden;
            /* bg-dark handled by global css */
        }
        
        #carousel-container {
            position: relative;
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .carousel-item-custom {
            position: absolute;
            width: 100%;
            height: 100%;
            display: none;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            opacity: 0;
            transition: opacity 1s ease-in-out;
        }
        
        .carousel-item-custom.active {
            display: flex;
            opacity: 1;
        }
        
        .participant-image {
            max-width: 90%;
            max-height: 70vh;
            object-fit: contain;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(255, 255, 255, 0.2);
        }
        
        .participant-info {
            max-width: 600px;
            margin-top: 30px;
        }
        
        .participant-info h2 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .participant-info p {
            font-size: 1.3rem;
            margin: 0;
            opacity: 0.9;
        }
        
        .logo {
            position: absolute;
            top: 30px;
            left: 30px;
            color: var(--text-primary);
            font-size: 2rem;
            font-weight: 700;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.5);
        }
        
        .counter {
            position: absolute;
            top: 30px;
            right: 30px;
            color: var(--primary-color);
            font-size: 1.2rem;
            background: var(--accent-color);
            padding: 10px 20px;
            border-radius: 50px;
            font-weight: bold;
            box-shadow: 0 0 15px rgba(242, 174, 61, 0.4);
        }
        
        .no-participants {
            color: white;
            text-align: center;
            padding: 50px;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.9); }
            to { opacity: 1; transform: scale(1); }
        }
        
        .carousel-item-custom.active .participant-image {
            animation: fadeIn 1s ease-out;
        }
    </style>
</head>
<body>
    <div class="bg-animation"></div>
    <div class="logo">
        <i class="bi bi-robot text-accent"></i> FutureLab AI
    </div>
    
    <div class="counter">
        <i class="bi bi-people-fill"></i> <span id="participant-count">0</span> participantes
    </div>
    
    <div id="carousel-container">
        <div id="no-participants" class="no-participants">
            <h1><i class="bi bi-hourglass-split"></i></h1>
            <h2>Esperando participantes...</h2>
            <p>Las imágenes generadas aparecerán aquí automáticamente</p>
        </div>
    </div>
    
    <script>
        let participants = [];
        let currentIndex = 0;
        let lastId = 0;
        const CAROUSEL_INTERVAL = 5000; // 5 segundos
        const POLLING_INTERVAL = 5000; // 5 segundos
        
        // Cargar participantes
        async function loadParticipants() {
            try {
                const response = await fetch('<?php echo BASE_URL; ?>/api/public/latest?since_id=' + lastId);
                const data = await response.json();
                
                if (data.ok && data.items.length > 0) {
                    // Agregar nuevos participantes al principio
                    participants = [...data.items.reverse(), ...participants];
                    lastId = data.last_id;
                    
                    updateCounter();
                    renderCarousel();
                }
            } catch (err) {
                console.error('Error loading participants:', err);
            }
        }
        
        // Renderizar carrusel
        function renderCarousel() {
            const container = document.getElementById('carousel-container');
            
            if (participants.length === 0) {
                document.getElementById('no-participants').style.display = 'block';
                return;
            }
            
            document.getElementById('no-participants').style.display = 'none';
            
            // Limpiar contenedor
            container.innerHTML = '';
            
            // Crear items
            participants.forEach((participant, index) => {
                const item = document.createElement('div');
                item.className = 'carousel-item-custom';
                if (index === currentIndex) {
                    item.classList.add('active');
                }
                
                item.innerHTML = `
                    <img src="${participant.result_image_url}" 
                         alt="${participant.name}" 
                         class="participant-image">
                    <div class="participant-info card text-center p-4">
                        <h2>${participant.name}</h2>
                        <p><i class="bi bi-mortarboard-fill"></i> ${participant.career}</p>
                    </div>
                `;
                
                container.appendChild(item);
            });
        }
        
        // Siguiente slide
        function nextSlide() {
            if (participants.length === 0) return;
            
            const items = document.querySelectorAll('.carousel-item-custom');
            items[currentIndex].classList.remove('active');
            
            currentIndex = (currentIndex + 1) % participants.length;
            
            items[currentIndex].classList.add('active');
        }
        
        // Actualizar contador
        function updateCounter() {
            document.getElementById('participant-count').textContent = participants.length;
        }
        
        // Iniciar
        async function init() {
            await loadParticipants();
            
            // Auto-avance del carrusel
            setInterval(nextSlide, CAROUSEL_INTERVAL);
            
            // Auto-refresh de datos
            setInterval(loadParticipants, POLLING_INTERVAL);
        }
        
        init();
    </script>
</body>
</html>

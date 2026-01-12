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
        let rotationTimer = null;
        let isShowingNew = false;
        
        const CAROUSEL_INTERVAL = 6000; // 6 segundos por imagen en rotación normal
        const NEW_IMAGE_DISPLAY_TIME = 15000; // 15 segundos para mostrar una imagen NUEVA
        const POLLING_INTERVAL = 4000; // 4 segundos polling
        
        // Cargar participantes
        async function loadParticipants() {
            try {
                // Siempre pedir los últimos 20 (ordenados por fecha desc)
                // No usamos since_id para evitar problemas con actualizaciones de registros viejos
                const url = '<?php echo BASE_URL; ?>/api/public/latest?limit=20';
                const response = await fetch(url);
                const data = await response.json();
                
                if (data.ok && data.items.length > 0) {
                    const latestItems = data.items; // Vienen ordenados DESC (más nuevo primero)
                    
                    if (participants.length === 0) {
                        // Carga inicial
                        participants = latestItems;
                        updateCounter();
                        renderCarousel();
                        startRotation();
                        return;
                    }
                    
                    // Verificar si el más reciente es diferente al que tenemos
                    const currentNewestId = participants[0].id;
                    const incomingNewestId = latestItems[0].id;
                    
                    // También verificamos si la fecha de actualización cambió (útil si se regenera el mismo ID)
                    // Pero para simplicidad, comparamos URL de la imagen
                    const currentImgUrl = participants[0].result_image_url;
                    const incomingImgUrl = latestItems[0].result_image_url;
                    
                    if (incomingNewestId !== currentNewestId || incomingImgUrl !== currentImgUrl) {
                        console.log("¡Nueva imagen detectada!", latestItems[0].name);
                        
                        // Actualizar lista completa
                        participants = latestItems;
                        updateCounter();
                        
                        // Forzar render para actualizar DOM
                        renderCarousel(true);
                        
                        // MOSTRAR LA NUEVA INMEDIATAMENTE
                        showNewImage();
                    }
                    // Si no hay cambios en la más reciente, no hacemos nada (seguimos rotando las viejas)
                }
            } catch (err) {
                console.error('Error loading participants:', err);
            }
        }
        
        function showNewImage() {
            // Detener rotación actual
            if (rotationTimer) clearInterval(rotationTimer);
            isShowingNew = true;
            
            // Ir al índice 0 (la más nueva)
            currentIndex = 0;
            showSlide(currentIndex);
            
            // Reiniciar rotación después de un tiempo prolongado
            console.log(`Mostrando nueva imagen por ${NEW_IMAGE_DISPLAY_TIME/1000}s...`);
            setTimeout(() => {
                isShowingNew = false;
                startRotation();
            }, NEW_IMAGE_DISPLAY_TIME);
        }
        
        function startRotation() {
            if (rotationTimer) clearInterval(rotationTimer);
            
            // Iniciar rotación
            rotationTimer = setInterval(() => {
                if (!isShowingNew && participants.length > 1) {
                    nextSlide();
                }
            }, CAROUSEL_INTERVAL);
        }
        
        // Renderizar carrusel
        function renderCarousel(preserveActive = false) {
            const container = document.getElementById('carousel-container');
            
            if (participants.length === 0) {
                document.getElementById('no-participants').style.display = 'block';
                return;
            }
            
            document.getElementById('no-participants').style.display = 'none';
            
            // Si preservamos, solo agregamos los nuevos si es necesario, 
            // pero por simplicidad vamos a reconstruir y restaurar clase active
            container.innerHTML = '';
            
            // Crear items
            participants.forEach((participant, index) => {
                const item = document.createElement('div');
                item.className = 'carousel-item-custom';
                item.id = `slide-${index}`;
                
                // Mostrar si es el actual
                if (index === currentIndex) {
                    item.classList.add('active');
                }
                
                // Precargar solo las primeras 3 imágenes para ahorrar ancho de banda
                const imgSrc = (index < 3 || Math.abs(index - currentIndex) < 2) 
                    ? participant.result_image_url 
                    : ''; // Lazy load manual
                
                item.innerHTML = `
                    <img src="${participant.result_image_url}" 
                         alt="${participant.name}" 
                         class="participant-image"
                         loading="${index < 2 ? 'eager' : 'lazy'}">
                    <div class="participant-info card text-center p-4">
                        <h2>${participant.name}</h2>
                        <p><i class="bi bi-mortarboard-fill"></i> ${participant.career}</p>
                    </div>
                `;
                
                container.appendChild(item);
            });
        }
        
        function showSlide(index) {
            const items = document.querySelectorAll('.carousel-item-custom');
            if (items.length === 0) return;
            
            // Quitar active de todos
            items.forEach(item => item.classList.remove('active'));
            
            // Asegurar índice válido
            if (index >= items.length) index = 0;
            currentIndex = index;
            
            // Activar nuevo
            if (items[currentIndex]) {
                items[currentIndex].classList.add('active');
            }
        }
        
        // Siguiente slide
        function nextSlide() {
            if (participants.length === 0) return;
            showSlide(currentIndex + 1);
        }
        
        // Actualizar contador
        function updateCounter() {
            document.getElementById('participant-count').textContent = participants.length;
        }
        
        // Iniciar
        function init() {
            loadParticipants();
            
            // Polling para nuevas imágenes
            setInterval(loadParticipants, POLLING_INTERVAL);
        }
        
        // Iniciar cuando el DOM esté listo
        document.addEventListener('DOMContentLoaded', init);
    </script>
</body>
</html>

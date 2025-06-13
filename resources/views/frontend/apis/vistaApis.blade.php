<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>APIs</title>

    <!-- Enlaces CDN de Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" xintegrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- Enlaces CDN de LeafletJS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha200-zGFVdEC8D7X3B2Fw6zL8J2d+qL2l0aC0o/f1uT1p8w==" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha200-gqN2S/jH2d+qL2l0aC0o/f1uT1p8w==" crossorigin=""></script>
    
    
    <ul class="nav flex-column">
        <li class="nav-item">
            <a class="nav-link active" href="{{ route('login') }}">
                Home
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ route('apis.index') }}">
                APIs
            </a>
        </li>
    </ul>
</head>

<body>
    <!-- Contenedor para Geolocalización -->
    <div class="container mt-4">
        <h1 class="mb-3">Ubicación</h1>

        <div id="location-info" class="alert alert-info">
            Cargando ubicación...
        </div>

        <div id="mapid" style="height: 400px; width: 100%;" class="mb-4 rounded shadow-sm"></div>
    </div>

    <!-- Contenedor para Dibujo con Canvas -->
    <div class="container mt-5 mb-5">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h2 class="card-title mb-0">Zona de Dibujo Libre</h2>
            </div>
            <div class="card-body d-flex flex-column align-items-center">
                <canvas id="drawingCanvas" class="border border-secondary mb-3 rounded" 
                style="width: 100%; max-width: 800px; height: 400px; background-color: white;"></canvas>
                <button id="saveDrawingButton" class="btn btn-success">Guardar Dibujo como JPG</button>
            </div>
        </div>
    </div>

    <!-- Contenedor para Captura desde Cámara -->
    <div class="container mt-5">
        <h2 class="mb-3">Captura desde cámara</h2>
        
        <div class="row mb-3">
            <div class="col-md-8">
                <div id="camera-container" class="border rounded p-2">
                    <video id="video" width="100%" height="auto" autoplay class="img-fluid rounded"></video>
                    <canvas id="canvas" width="640" height="480" style="display:none;"></canvas>
                </div>
            </div>
            <div class="col-md-4 d-flex flex-column justify-content-center">
                <button id="capture" class="btn btn-success mb-2">Tomar Foto</button>
                <a id="downloadLink" style="display: none;" download="captura.png" class="btn btn-secondary">Descargar Imagen</a>
            </div>
        </div>
        
        <div id="snapshot-container" class="mt-4">
            <h3>Captura:</h3>
            <img id="snapshot" src="" alt="Imagen capturada" class="img-fluid border rounded shadow-sm" />
        </div>
    </div>

    

    <!-- Scripts -->
    <script>
        // Script para geolocalización
            document.addEventListener('DOMContentLoaded', function() {
            const locationInfo = document.getElementById('location-info');

            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        const latitude = position.coords.latitude;
                        const longitude = position.coords.longitude;
                        const zoom = 16;
                        locationInfo.innerHTML = `Latitud: ${latitude}<br>Longitud: ${longitude}`;
                        const mymap = L.map('mapid').setView([latitude, longitude], zoom);
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            maxZoom: 19,
                            attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
                        }).addTo(mymap);
                        const marker = L.marker([latitude, longitude]).addTo(mymap);
                        marker.bindPopup(`<b>¡Aquí estoy!</b>`).openPopup();
                    },
                    function(error) {
                        switch(error.code) {
                            case error.PERMISSION_DENIED:
                                locationInfo.innerHTML = "Acceso a geolocalización denegado.";
                                break;
                            case error.POSITION_UNAVAILABLE:
                                locationInfo.innerHTML = "Ubicación no disponible.";
                                break;
                            case error.TIMEOUT:
                                locationInfo.innerHTML = "Tiempo de espera agotado.";
                                break;
                            case error.UNKNOWN_ERROR:
                                locationInfo.innerHTML = "Error desconocido.";
                                break;
                        }
                    }
                );
            } else {
                locationInfo.innerHTML = "El navegador no soporta la geolocalización.";
            }
        });

        // Script para el dibujo de canvas
        const drawingCanvas = document.getElementById('drawingCanvas');
        const drawingCtx = drawingCanvas.getContext('2d'); 
        const saveDrawingButton = document.getElementById('saveDrawingButton');

        let isDrawing = false; // Variable para controlar si el usuario está dibujando
        let lastX = 0;         // Coordenada X de la última posición del mouse
        let lastY = 0;         // Coordenada Y de la última posición del mouse

        
        function resizeDrawingCanvas() {
            const imageData = drawingCtx.getImageData(0, 0, drawingCanvas.width, drawingCanvas.height);

            drawingCanvas.width = drawingCanvas.offsetWidth;   
            drawingCanvas.height = drawingCanvas.offsetHeight; 

            // Rellena el fondo de color blanco antes de restaurar el dibujo
            drawingCtx.fillStyle = 'white';
            drawingCtx.fillRect(0, 0, drawingCanvas.width, drawingCanvas.height);

            // Restaura el contenido del canvas después de redimensionar
            drawingCtx.putImageData(imageData, 0, 0);

            drawingCtx.strokeStyle = 'black'; // Color de la línea
            drawingCtx.lineWidth = 2;         // Grosor de la línea
            drawingCtx.lineJoin = 'round';    // Forma de las uniones de línea
            drawingCtx.lineCap = 'round';     // Forma de los extremos de línea
        }

        
        function getMousePosDrawing(canvasElement, evt) {
            const rect = canvasElement.getBoundingClientRect(); // Obtiene el tamaño y posición del canvas
            return {
                x: evt.clientX - rect.left, 
                y: evt.clientY - rect.top   
            };
        }

        function handleDrawingMouseDown(e) {
            isDrawing = true; // Indica que se está dibujando
            const { x, y } = getMousePosDrawing(drawingCanvas, e);
            [lastX, lastY] = [x, y]; // Guarda la posición inicial
        }

        
        function handleDrawingMouseMove(e) {
            if (!isDrawing) return; // Si no se está dibujando, sale

            const { x, y } = getMousePosDrawing(drawingCanvas, e);

            drawingCtx.beginPath();       // Iniciar un nuevo trazo
            drawingCtx.moveTo(lastX, lastY); // Mover el lápiz a la última posición
            drawingCtx.lineTo(x, y);      // Dibujar una línea hasta la posición actual
            drawingCtx.stroke();          // Renderizar el trazo

            [lastX, lastY] = [x, y]; 
        }

        function stopDrawing() {
            isDrawing = false; // Detiene el dibujo
        }

        
        function saveDrawingCanvasAsJPG() {
            const imageDataURL = drawingCanvas.toDataURL('image/jpeg', 0.9);

            // Crear un elemento 'a' temporal para descargar la imagen
            const link = document.createElement('a');
            link.href = imageDataURL;            // Establecer la URL de la imagen
            link.download = 'dibujo_canvas.jpg'; // Nombre del archivo a descargar
            document.body.appendChild(link);     // Añadir el enlace al DOM
            link.click();                        // Clic en el enlace para iniciar la descarga
            document.body.removeChild(link);     // Remover el enlace del DOM
        }

        
        window.addEventListener('load', function() {
            resizeDrawingCanvas(); // Ajusta el tamaño inicial del canvas de dibujo

            // Inicializa el fondo del canvas de dibujo con color blanco
            drawingCtx.fillStyle = 'white';
            drawingCtx.fillRect(0, 0, drawingCanvas.width, drawingCanvas.height);

            // Añade 'EventListeners' al dibujo
            drawingCanvas.addEventListener('mousedown', handleDrawingMouseDown);
            drawingCanvas.addEventListener('mousemove', handleDrawingMouseMove);
            drawingCanvas.addEventListener('mouseup', stopDrawing);
            drawingCanvas.addEventListener('mouseout', stopDrawing);

            // Añade 'EventListener' al botón de guardar dibujo
            saveDrawingButton.addEventListener('click', saveDrawingCanvasAsJPG);
        });

        // Escuchar el evento de redimensionamiento de la ventana para ajustar el canvas de dibujo
        window.addEventListener('resize', resizeDrawingCanvas);

        // Script para captura desde cámara
        window.addEventListener('load', function() { 
            const video = document.getElementById('video');
            const canvasCamera = document.getElementById('canvas'); 
            const captureButton = document.getElementById('capture');
            const snapshot = document.getElementById('snapshot');
            const downloadLink = document.getElementById('downloadLink');

            if (video && captureButton) { 
                navigator.mediaDevices.getUserMedia({ video: true })
                    .then(stream => {
                        video.srcObject = stream;
                    })
                    .catch(err => {
                        const cameraContainer = document.getElementById('camera-container');
                        if (cameraContainer) {
                            cameraContainer.innerHTML = '<div class="alert alert-warning" role="alert">No se pudo acceder a la cámara. Por favor, asegúrate de haber dado los permisos necesarios.</div>';
                        } else {
                            console.error("No se pudo acceder a la cámara:", err); 
            }
                    });

                captureButton.addEventListener('click', () => {
                    const context = canvasCamera.getContext('2d');
                    canvasCamera.width = video.videoWidth; 
                    canvasCamera.height = video.videoHeight;
                    context.drawImage(video, 0, 0, canvasCamera.width, canvasCamera.height);
                    const imageDataUrl = canvasCamera.toDataURL('image/png');
                    snapshot.src = imageDataUrl;
                    downloadLink.href = imageDataUrl;
                    downloadLink.style.display = 'inline-block';
                });
            }
        });
    </script>

    <!-- Enlaces CDN de Bootstrap JS (bundle con Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" xintegrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
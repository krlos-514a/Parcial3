<!DOCTYPE html>
<html lang="es">

    <head>
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
        <div class="container">
            <h1>Ubicación</h1>

            <div id="location-info" class="alert alert-info">
                Cargando ubicación...
            </div>

            <div id="mapid" style="height: 400px; width: 100%;"></div>

        </div>

        <script>
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

        const video = document.getElementById('video');
        const canvas = document.getElementById('canvas');
        const captureButton = document.getElementById('capture');
        const snapshot = document.getElementById('snapshot');
        const downloadLink = document.getElementById('downloadLink');

        navigator.mediaDevices.getUserMedia({ video: true })
            .then(stream => {
                video.srcObject = stream;
            })
            .catch(err => {
                alert("No se pudo acceder a la cámara.");
            });

        captureButton.addEventListener('click', () => {
            const context = canvas.getContext('2d');
            context.drawImage(video, 0, 0, canvas.width, canvas.height);
            const imageDataUrl = canvas.toDataURL('image/png');
            snapshot.src = imageDataUrl;
            downloadLink.href = imageDataUrl;
            downloadLink.style.display = 'inline-block';
        });
        </script>
    </body>

    <div class="container">
        <h2>Captura desde cámara</h2>
    
        <div id="camera-container">
            <video id="video" width="640" height="480" autoplay></video>
            <canvas id="canvas" width="640" height="480" style="display:none;"></canvas>
        </div>
    
        <button id="capture">Tomar Foto</button>
        <a id="downloadLink" style="display: none;" download="captura.png">Descargar Imagen</a>
    
        <div id="snapshot-container" style="margin-top: 20px;">
            <h3>Captura:</h3>
            <img id="snapshot" src="" alt="Imagen capturada" />
        </div>
    </div>
</html>
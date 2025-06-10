<!DOCTYPE html>
<html lang="es">

    <head>
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
                        locationInfo.innerHTML = `Latitud: ${latitude}<br>Longitud: ${longitude}`;
                        initMap(latitude, longitude);
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
        </script>
    </body>
</html>
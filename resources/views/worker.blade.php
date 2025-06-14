@extends('backend.menus.superior')

@section('content-admin-css')
    <link href="{{ asset('css/adminlte.min.css') }}" type="text/css" rel="stylesheet" />
    <link href="{{ asset('css/dataTables.bootstrap4.css') }}" type="text/css" rel="stylesheet" />
    <link href="{{ asset('css/toastr.min.css') }}" type="text/css" rel="stylesheet" />
    <link href="{{ asset('css/buttons_estilo.css') }}" rel="stylesheet">
@stop

<div id="divcontenedor">

 <section class="content-header">
        <div class="container-fluid">
            <div class="col-sm-12">
                <h2>Web Worker</h2>
                <h1>Ordenamiento de números aleatorios</h1>
            </div>
            <br>
        <button id="run" class="button button-primary button-pill button-small">
                Iniciar ordenamiento
                <i class="fas fa-play"></i>
            </button>
        </div>
    </section>

    <section class="container-fluid" style="padding: 20px">
        <p id="estado" class="mt-3 text-muted"></p>
        <ol id="resultado" class="mt-3 list-group"></ol>
    </section>

    <script>
        document.getElementById('run').addEventListener('click', () => {
            const worker = new Worker("{{ asset('js/worker.js') }}");

            const data = Array.from({ length: 100000 }, () => Math.floor(Math.random() * 1_000_000));
            
            const cantidad = 50;

            document.getElementById('resultado').textContent = 'Enviando números a procesar...';

            worker.postMessage(data);

            worker.onmessage = (e) => {
                const msg = e.data;
                const resultado = document.getElementById('resultado');

                if (msg.status === 'ok') {
                    document.getElementById('estado').textContent = 'Ordenamiento completado. Mostrando los primeros ' + cantidad + ' numeros.';
                    resultado.innerHTML = '';
                    
                    msg.data.slice(0, cantidad).forEach(num => {
                        const li = document.createElement('li');
                        li.className = 'list-group-item';
                        li.textContent = num;
                        resultado.appendChild(li);
                    });
                } else {
                    document.getElementById('resultado').textContent = 'Ocurrió un error en el web worker';
                    resultado.innerHTML = `<li>${msg.message}</li>`;
                }

                worker.terminate();
            };

            worker.onerror = (err) => {
                document.getElementById('resultado').textContent = 'Ocurrió un error con el Web Worker';
                console.error(err);
                worker.terminate();
            };
        });
    </script>

</div>

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="@yield('meta_description', 'Sistema de gestión para car wash, ventas y operaciones')" />
    <meta name="author" content="SakCode" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>{{ config('app.name', 'CarWash ESP') }} - @yield('title')</title>
    @stack('css-datatable')
    @vite(['resources/css/app.css'])
    @stack('css')
</head>

<body class="sb-nav-fixed">
    <a class="app-skip-link" href="#main-content">Saltar al contenido principal</a>

    <x-navigation-header />

    <div id="layoutSidenav">

        <x-navigation-menu />

        <div id="layoutSidenav_content">

            <main id="main-content" class="app-main" tabindex="-1">
                @yield('content')
            </main>

            <x-footer />

        </div>
    </div>

    <div class="modal fade" id="globalConfirmModal" tabindex="-1" aria-labelledby="globalConfirmModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="globalConfirmModalLabel">Confirmar acción</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body" id="globalConfirmModalMessage">
                    ¿Desea continuar con esta acción?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" id="globalConfirmModalAccept">Confirmar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/scripts.js') }}"></script>
    @vite(['resources/js/app.js'])
    @stack('js')

</body>


</html>
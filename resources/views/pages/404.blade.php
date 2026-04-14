<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Error 404 - {{ config('app.name', 'CarWash ESP') }}</title>
        <link href="{{ asset('css/styles.css') }}" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/public.js'])
    </head>
    <body>
        <div id="layoutError">
            <div id="layoutError_content">
                <main>
                    <div class="container">
                        <div class="row justify-content-center">
                            <div class="col-lg-6">
                                <div class="text-center mt-4">
                                    <img class="mb-4 img-error" src="{{ asset('assets/img/error-404-monochrome.svg') }}" />
                                    <p class="lead">La URL solicitada no fue encontrada en este servidor.</p>
                                    <a href="{{ route('panel') }}">
                                        <i class="fas fa-arrow-left me-1"></i>
                                        Volver al panel
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </main>
            </div>
            <div id="layoutError_footer">
                <footer class="py-4 bg-light mt-auto">
                    <div class="container-fluid px-4">
                        <div class="d-flex align-items-center justify-content-between small">
                            <div class="text-muted">&copy; {{ now()->year }} {{ config('app.name', 'CarWash ESP') }}. Todos los derechos reservados.</div>
                            <div>
                                <a href="#">Política de Privacidad</a>
                                &middot;
                                <a href="#">Términos y Condiciones</a>
                            </div>
                        </div>
                    </div>
                </footer>
            </div>
        </div>
        <script src="{{ asset('js/scripts.js') }}"></script>
    </body>
</html>

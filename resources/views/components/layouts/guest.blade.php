<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="antialiased bg-gray-100">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0">
            <div>

            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
                {{ $slot }}
            </div>
        </div>
        @livewireScripts
        <script>
            document.addEventListener('livewire:init', () => {
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                    didOpen: (toast) => {
                        toast.addEventListener('mouseenter', Swal.stopTimer)
                        toast.addEventListener('mouseleave', Swal.resumeTimer)
                    }
                });

                @if (session('status') || session('message'))
                    Toast.fire({
                        icon: @js(session('alert-type', 'success')),
                        title: @js(session('status') ?? session('message'))
                    });
                @endif

                window.addEventListener('show-alert', event => {
                    const detail = event.detail;
                    const message = detail.message || (detail[0] ? detail[0].message : 'Pesan tidak ditemukan.');
                    const type = detail.type || (detail[0] ? detail[0].type : 'success');

                    Toast.fire({
                        icon: type,
                        title: message
                    });
                });
            });
        </script>
    </body>
</html>

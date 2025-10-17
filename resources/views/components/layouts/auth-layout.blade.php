<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name', 'Laravel') }}</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @livewireStyles
</head>
<body class="min-h-screen flex flex-col items-center justify-center bg-gray-50 sm:bg-gray-100 p-4 font-sans">
    {{ $slot }}
    @livewireScripts
</body>
</html>
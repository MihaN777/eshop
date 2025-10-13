<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name'))</title>

    <!-- Styles / Scripts -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite([
            'resources/css/app.css',
            'resources/sass/main.sass',
            'resources/js/app.js'
            ])
    @endif
</head>

<body class="antialiased" x-data="{ 'showTaskUploadModal': false, 'showTaskEditModal': false }" x-cloak>

@include('shared.header')

<main class="md:min-h-screen md:flex md:items-center md:justify-center py-16 lg:py-20">
    <div class="container">
        @include('shared.flash')

        <div class="text-center">
            <a href="{{ route('home')  }}" class="inline-block" rel="home">
                <img src="{{ Vite::image('logo.svg') }}"
                     class="w-[148px] md:w-[201px] h-[36px] md:h-[50px]" alt="CutCode">
            </a>
        </div>

        @yield('content')
    </div>
</main>

@include('shared.footer')

<script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.x.x/dist/alpine.min.js" defer></script>

</body>
</html>

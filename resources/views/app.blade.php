<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'NPPC — National Political Prisoner Coalition')</title>
    @include('layout.head')
    @yield('head')
</head>
<body class="font-sans antialiased bg-white text-gray-900 page-{{ \Illuminate\Support\Str::slug(trim($__env->yieldContent('title','page'))) }}">
    @include('layout.nav')
    <main>
        @yield('body')
    </main>
    @include('layout.footer')
</body>
</html>

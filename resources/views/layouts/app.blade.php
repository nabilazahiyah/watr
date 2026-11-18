<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Watr.id')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans text-slate-800 antialiased">

    @php
    $solid = View::hasSection('navbar_solid');
    $noNav = View::hasSection('hide_navbar');
@endphp

@unless ($noNav)
    @include('partials.navbar', ['solid' => $solid])
@endunless

<main class="{{ $solid && ! $noNav ? 'pt-20' : '' }}">
    @yield('content')
</main>

@unless ($noNav)
    @include('partials.footer')
@endunless

@stack('scripts')

</body>
</html>
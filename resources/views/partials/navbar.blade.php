@php
    $solid = $solid ?? false;

    $isHome     = request()->routeIs('home');
    $isJelajahi = request()->routeIs('jelajahi');

    // Warna teks: putih di Beranda, gelap di Jelajahi
    $teks   = $solid ? 'text-slate-800' : 'text-white';
    $aktif  = 'font-bold ' . $teks;
    $biasa  = 'font-normal transition ' . ($solid ? 'text-slate-600 hover:text-slate-900' : 'text-white/90 hover:text-white');
@endphp

<header class="{{ $solid ? 'fixed bg-white' : 'absolute' }} inset-x-0 top-0 z-1100">

    {{-- Shadow gradasi: hanya di Beranda --}}
    @unless ($solid)
        <div class="pointer-events-none absolute inset-x-0 top-0 -z-10 h-28 bg-linear-to-b from-black/40 to-transparent"></div>
    @endunless

    <nav class="mx-auto flex max-w-7xl items-center justify-between px-6 py-5">

        {{-- Logo --}}
        <a href="{{ route('home') }}#beranda">
            <img src="{{ asset('images/logo.svg') }}" alt="Watr.id" class="h-10">
        </a>

        {{-- Menu desktop --}}
        <ul class="hidden items-center gap-8 text-sm md:flex">
            <li><a href="{{ route('home') }}#beranda" class="{{ $isHome ? $aktif : $biasa }}">Beranda</a></li>
            <li><a href="{{ route('jelajahi') }}" class="{{ $isJelajahi ? $aktif : $biasa }}">Jelajahi</a></li>
            <li><a href="{{ route('home') }}#favorit" class="{{ $biasa }}">Favorit</a></li>
            <li><a href="{{ route('home') }}#tentang" class="{{ $biasa }}">Tentang</a></li>
        </ul>

        {{-- Bahasa --}}
        <div class="relative hidden md:block">
            <button id="lang-btn" type="button"
                    class="flex items-center gap-2 text-sm font-semibold {{ $teks }}">
                <span class="flex size-8 flex-col overflow-hidden rounded-full">
                    <span class="h-1/2 w-full bg-[#F04E67]"></span>
                    <span class="h-1/2 w-full bg-[#F8F8F8] {{ $solid ? 'border-b border-x border-slate-200 rounded-b-full' : '' }}"></span>
                </span>
                <span>ID</span>
                <svg id="lang-arrow" class="size-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <ul id="lang-menu"
                class="absolute right-0 mt-3 hidden w-40 overflow-hidden rounded-xl bg-white py-1 text-sm text-slate-700 shadow-lg">
                <li>
                    <a href="#" class="flex items-center gap-3 px-4 py-2 hover:bg-slate-100">
                        <span class="flex size-6 flex-col overflow-hidden rounded-full">
                            <span class="h-1/2 w-full bg-[#F04E67]"></span>
                            <span class="h-1/2 w-full bg-[#F8F8F8]"></span>
                        </span>
                        Indonesia
                    </a>
                </li>
            </ul>
        </div>

        {{-- Tombol menu mobile --}}
        <button id="menu-btn" type="button" class="{{ $teks }} md:hidden" aria-label="Menu">
            <svg class="size-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>
    </nav>

    {{-- Menu mobile --}}
    <ul id="mobile-menu" class="hidden space-y-3 bg-white px-6 py-4 text-sm text-slate-700 md:hidden">
        <li><a href="{{ route('home') }}#beranda" class="{{ $isHome ? 'font-bold' : '' }}">Beranda</a></li>
        <li><a href="{{ route('jelajahi') }}" class="{{ $isJelajahi ? 'font-bold' : '' }}">Jelajahi</a></li>
        <li><a href="{{ route('home') }}#favorit">Favorit</a></li>
        <li><a href="{{ route('home') }}#tentang">Tentang</a></li>
    </ul>
</header>
@extends('layouts.app')

@section('title', 'Watr.id - Depot Air Minum Terdekat')

@section('content')

<section id="beranda"
         class="relative flex min-h-screen items-center justify-center overflow-hidden
                bg-gradient-to-b from-sky-100 via-sky-50 to-white bg-cover bg-center"
         style="background-image: url('{{ asset('images/asset-hero.png') }}')">

    {{-- Konten tengah --}}
    <div class="relative z-10 mx-auto max-w-3xl px-6 pt-24 text-center">
        <h1 class="text-3xl font-semibold leading-tight text-forest md:text-5xl">
            Temukan Depot Air Minum Isi Ulang Terdekat
            di <span class="text-green-700">Bogor Tengah</span>
        </h1>

        <p class="mx-auto mt-6 max-w-xl text-sm leading-relaxed text-slate-700">
            Akses pemetaan interaktif untuk menjelajahi lokasi depot air minum di seluruh wilayah
            Kecamatan Bogor Tengah. Cek informasi harga galon, status operasional, hingga
            ketersediaan layanan pesan antar dengan cepat dan mudah.
        </p>

        {{-- Search bar --}}
        <form action="{{ route('jelajahi') }}" method="GET" class="mx-auto mt-12 flex max-w-xl items-center gap-2 rounded-full bg-white p-2 pl-5 shadow-lg">
            <svg class="h-5 w-5 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <circle cx="11" cy="11" r="7" stroke-width="2"/>
                <path stroke-linecap="round" stroke-width="2" d="M21 21l-4.3-4.3"/>
            </svg>
            <input type="text" name="q" placeholder="Cari depot atau kelurahan..."
                   class="w-full bg-transparent text-sm outline-none placeholder:text-slate-400">
            <button type="submit"
                    class="shrink-0 rounded-full bg-brand-blue px-6 py-2.5 text-sm font-medium text-white
                           transition hover:bg-brand-dark">
                Cari Sekarang
            </button>
        </form>
    </div>

    <div class="absolute inset-x-0 bottom-0 z-10 bg-brand-yellow py-2 text-center text-xs font-medium text-slate-900">
        Untuk pengaduan hubungi watr.id@gmail.com
    </div>

    <a href="#jelajahi" class="absolute bottom-8 left-1/2 z-10 -translate-x-1/2 animate-bounce text-white">
        <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </a>
</section>

<section id="tentang" class="bg-white py-20 md:py-28">
    <div class="mx-auto grid max-w-6xl items-center gap-12 px-6 md:grid-cols-2">

        {{-- Kolase foto --}}
        <div class="flex justify-center">
            <img src="{{ asset('images/about-collage.png') }}"
                 alt="Depot air minum isi ulang"
                 class="w-full max-w-md md:max-w-none">
        </div>

        {{-- Teks --}}
        <div>
            <p class="text-xs font-semibold text-brand-green-2">Tentang WATR</p>

            <h2 class="mt-3 text-2xl font-semibold leading-snug text-brand-green-3 md:text-3xl">
                Memudahkan Akses Informasi Air Minum Isi Ulang Terpercaya
            </h2>

            <p class="mt-4 text-sm leading-relaxed text-slate-600">
                Dibangun untuk warga, pendatang, dan siapa pun yang membutuhkan akses cepat
                ke informasi depot air minum di pusat Kota Bogor.
            </p>

            {{-- Statistik --}}
            <dl class="mt-8 grid grid-cols-2 gap-x-8 gap-y-6">
                @foreach ([
                    ['10+', 'Kelurahan'],
                    ['50+', 'Depot Air Terdata'],
                    ['99%', 'Terverifikasi Lokasi Presisi'],
                    ['24/7', 'Akses Peta Interaktif'],
                ] as [$angka, $label])
                    <div>
                        <dt class="text-2xl font-semibold text-brand-green-3">{{ $angka }}</dt>
                        <dd class="mt-1 text-xs text-slate-600">{{ $label }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>

    </div>
</section>

@php
    $rekomendasi = [
        ['nama' => 'Jaya Tirta Alam',   'rating' => '4,9', 'ulasan' => 59, 'foto' => 'jaya-tirta-alam.png',     'buka24' => false, 'antar' => true],
        ['nama' => 'SHiST Water Station','rating' => '5,0', 'ulasan' => 1,  'foto' => 'shist-water-station.png', 'buka24' => true,  'antar' => true],
        ['nama' => "TIN'S TIRTA",       'rating' => '5,0', 'ulasan' => 24, 'foto' => 'tins-tirta.png',          'buka24' => false, 'antar' => false],
    ];
@endphp

<section id="favorit" class="bg-brand-sky py-20 md:py-24">
    <div class="mx-auto max-w-5xl px-6">

        <div class="text-center">
            <p class="text-xs font-semibold text-brand-green-2">Pilihan Populer</p>
            <h2 class="mt-2 text-2xl font-semibold text-brand-green-3 md:text-3xl">
                Rekomendasi Depot Air Minum
                <span class="bg-brand-yellow px-1">Terpercaya</span>
            </h2>
        </div>

        <div id="filter-depot" class="mt-8 flex flex-wrap justify-center gap-3">
            @foreach ([['semua', 'Semua'], ['buka24', 'Buka 24 Jam'], ['antar', 'Layanan Antar']] as [$key, $label])
                <button type="button" data-filter="{{ $key }}"
                        class="filter-btn rounded-full border border-brand-blue px-5 py-1.5 text-xs font-medium transition
                               {{ $key === 'semua' ? 'bg-brand-blue text-white' : 'bg-white text-brand-blue hover:bg-brand-blue/10' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>

        <div class="mt-8 grid gap-6 md:grid-cols-3">
            @foreach ($rekomendasi as $d)
                <article class="depot-card rounded-xl bg-white p-3 shadow-lg shadow-slate-300/50"
                         data-buka24="{{ $d['buka24'] ? '1' : '0' }}"
                         data-antar="{{ $d['antar'] ? '1' : '0' }}">

                    <div class="relative">
                        <img src="{{ asset('images/depot/' . $d['foto']) }}" alt="{{ $d['nama'] }}"
                             class="aspect-4/3 w-full rounded-lg object-cover">

                        <button type="button" aria-label="Favorit"
                                class="fav-btn absolute right-2 top-2 text-white/80 transition hover:scale-110">
                            <svg class="size-6 drop-shadow" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 21s-7.5-4.6-9.6-9.2C.9 8.4 2.7 5 6 5c2 0 3.3 1.1 4 2.2h4C14.7 6.1 16 5 18 5c3.3 0 5.1 3.4 3.6 6.8C19.5 16.4 12 21 12 21z"/>
                            </svg>
                        </button>
                    </div>

                    <h3 class="mt-3 text-sm font-semibold text-brand-green-3">{{ $d['nama'] }}</h3>

                    <div class="mt-1 flex items-center gap-1.5 text-xs text-slate-600">
                        <span>{{ $d['rating'] }}</span>
                        <span class="text-yellow-400">★★★★★</span>
                        <span>· {{ $d['ulasan'] }} Google Reviews</span>
                    </div>

                    <a href="#"
                       class="mt-3 block rounded-lg border border-slate-300 py-2 text-center text-xs font-medium text-slate-700 transition hover:bg-slate-50">
                        Lihat Peta
                    </a>
                </article>
            @endforeach
        </div>

        <div class="mt-10 text-center">
            <a href="#"
               class="inline-block rounded-full bg-brand-blue px-7 py-2.5 text-sm font-medium text-white transition hover:brightness-95">
                Lihat Semua
            </a>
        </div>

    </div>
</section>

<section id="langkah" class="relative overflow-hidden bg-white pb-72 pt-20 md:pt-28">

    <img src="{{ asset('images/water-hero.png') }}" alt=""
         class="pointer-events-none absolute inset-x-0 bottom-0 w-full object-cover object-bottom">

    <div class="relative z-10 mx-auto max-w-6xl px-6">

        <div class="text-center">
            <p class="text-xs font-semibold text-brand-green-2">Mulai Pencarian</p>
            <h2 class="mt-2 text-2xl font-semibold text-slate-800 md:text-3xl">
                Tiga Langkah Sederhana
            </h2>
        </div>

        <div class="mt-16 grid gap-6 md:grid-cols-3">
            @foreach ([
                ['1', 'Buka Peta',        'Lihat persebaran depot di seluruh wilayah Bogor Tengah.'],
                ['2', 'Filter Kebutuhan', 'Pilih lokasi dan rentang harga yang sesuai.'],
                ['3', 'Hubungi Depot',    'Lihat informasi lengkap lalu hubungi depot pilihan Anda.'],
            ] as [$no, $judul, $desk])
                <article class="rounded-xl bg-white px-6 py-8 text-center shadow-lg shadow-slate-300/50">
                    <span class="mx-auto flex size-8 items-center justify-center rounded-full bg-brand-blue text-sm font-semibold text-white">
                        {{ $no }}
                    </span>
                    <h3 class="mt-4 text-base font-semibold text-slate-800">{{ $judul }}</h3>
                    <p class="mx-auto mt-4 max-w-[16rem] text-xs leading-relaxed text-slate-600">
                        {{ $desk }}
                    </p>
                </article>
            @endforeach
        </div>

    </div>
</section>
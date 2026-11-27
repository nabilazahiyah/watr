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
            Cari depot air minum di Bogor Tengah. Lihat lokasi, harga, jam buka, dan layanan antar sebelum memilih.
        </p>

        {{-- Search bar --}}
        <form action="{{ route('jelajahi') }}" method="GET" class="mx-auto mt-12 flex max-w-xl items-center gap-2 rounded-full bg-white p-2 pl-5 shadow-lg">
            <svg width="20" height="20" class="h-5 w-5 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <circle cx="11" cy="11" r="7" stroke-width="2"/>
                <path stroke-linecap="round" stroke-width="2" d="M21 21l-4.3-4.3"/>
            </svg>
            <input type="text" name="q" placeholder="Cari nama depot, alamat, atau kelurahan..."
                   class="w-full bg-transparent text-sm outline-none placeholder:text-slate-400">
            <button type="submit"
                    class="shrink-0 rounded-full bg-brand-blue px-6 py-2.5 text-sm font-medium text-white
                           transition hover:bg-brand-dark">
                Cari
            </button>
        </form>
    </div>

    <div class="absolute inset-x-0 bottom-0 z-10 bg-brand-yellow py-2 text-center text-xs font-medium text-slate-900">
        Ada pertanyaan atau koreksi? Hubungi watr.id@gmail.com.
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
                Informasi depot air minum di Bogor Tengah
            </h2>

            <p class="mt-4 text-sm leading-relaxed text-slate-600">
                WATR membantu warga dan pengunjung menemukan informasi depot air minum di Bogor Tengah.
            </p>

            {{-- Statistik dari data depot --}}
            <dl class="mt-8 grid grid-cols-2 gap-x-8 gap-y-6">
                @foreach ($statistics as [$angka, $label])
                    <div>
                        <dt class="text-2xl font-semibold text-brand-green-3">{{ $angka }}</dt>
                        <dd class="mt-1 text-xs text-slate-600">{{ $label }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>

    </div>
</section>

<section id="favorit" class="bg-brand-sky py-20 md:py-24">
    <div class="mx-auto max-w-5xl px-6">

        <div class="text-center">
            <p class="text-xs font-semibold text-brand-green-2">Depot di Bogor Tengah</p>
            <h2 class="mt-2 text-2xl font-semibold text-brand-green-3 md:text-3xl">
                Informasi depot
            </h2>
        </div>

        <div id="filter-depot" class="mt-8 flex flex-wrap justify-center gap-3">
            @foreach ([['semua', 'Semua'], ['buka', 'Buka Setiap Hari'], ['antar', 'Layanan Antar']] as [$key, $label])
                <button type="button" data-filter="{{ $key }}"
                        class="filter-btn rounded-full border border-brand-blue px-5 py-1.5 text-xs font-medium transition
                               {{ $key === 'semua' ? 'bg-brand-blue text-white' : 'bg-white text-brand-blue hover:bg-brand-blue/10' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>

        <div class="mt-8 grid gap-6 md:grid-cols-3">
            @forelse ($recommendedDepots as $d)
                <article class="depot-card rounded-xl bg-white p-3 shadow-lg shadow-slate-300/50"
                         data-buka="{{ $d['buka_setiap_hari'] ? '1' : '0' }}"
                         data-antar="{{ $d['antar'] ? '1' : '0' }}">

                    <div class="relative">
                        <img src="{{ asset('images/fotodummy.jpg') }}" alt="Ilustrasi depot air minum"
                             class="aspect-4/3 w-full rounded-lg object-cover">

                        <button type="button" data-favorite-id="{{ $d['id'] }}" aria-label="Tambahkan ke favorit" aria-pressed="false"
                                class="fav-btn absolute right-2 top-2 text-white/80 transition hover:scale-110">
                            <svg class="size-6 drop-shadow" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 21s-7.5-4.6-9.6-9.2C.9 8.4 2.7 5 6 5c2 0 3.3 1.1 4 2.2h4C14.7 6.1 16 5 18 5c3.3 0 5.1 3.4 3.6 6.8C19.5 16.4 12 21 12 21z"/>
                            </svg>
                        </button>
                    </div>

                    <h3 class="mt-3 text-sm font-semibold text-brand-green-3">{{ $d['nama'] }}</h3>
                    <p class="mt-1 truncate text-xs text-slate-500">{{ $d['alamat'] }} · {{ $d['kelurahan'] }}</p>

                    <div class="mt-2 space-y-1 text-xs text-slate-600">
                        <p class="font-medium text-brand-green-3">Rp{{ number_format($d['harga'], 0, ',', '.') }} per galon</p>
                        <p>Jam buka {{ $d['jam_buka'] }}–{{ $d['jam_tutup'] }} · {{ $d['antar'] ? 'Melayani antar' : 'Tidak melayani antar' }}</p>
                        <p>{{ count($d['hari_libur']) ? 'Hari libur: '.implode(', ', $d['hari_libur']) : 'Jadwal hari libur belum tersedia' }}</p>
                    </div>

                    <a href="{{ route('jelajahi', ['q' => $d['nama']]) }}"
                       class="mt-3 block rounded-lg border border-slate-300 py-2 text-center text-xs font-medium text-slate-700 transition hover:bg-slate-50">
                        Lihat di peta
                    </a>
                </article>
            @empty
                <p class="col-span-full rounded-xl bg-white p-8 text-center text-sm text-slate-600">
                    Informasi depot belum tersedia.
                </p>
            @endforelse
        </div>

        <div class="mt-10 text-center">
            <a href="{{ route('jelajahi') }}"
               class="inline-block rounded-full bg-brand-blue px-7 py-2.5 text-sm font-medium text-white transition hover:brightness-95">
                Lihat semua depot
            </a>
        </div>

    </div>
</section>

<section id="langkah" class="relative overflow-hidden bg-white pb-72 pt-20 md:pt-28">

    <img src="{{ asset('images/water-hero.png') }}" alt=""
         class="pointer-events-none absolute inset-x-0 bottom-0 w-full object-cover object-bottom">

    <div class="relative z-10 mx-auto max-w-6xl px-6">

        <div class="text-center">
            <p class="text-xs font-semibold text-brand-green-2">Cara menggunakan WATR</p>
            <h2 class="mt-2 text-2xl font-semibold text-slate-800 md:text-3xl">
                Temukan depot pilihanmu
            </h2>
        </div>

        <div class="mt-16 grid gap-6 md:grid-cols-3">
            @foreach ([
                ['1', 'Cari depot',        'Lihat lokasi depot di Bogor Tengah melalui peta.'],
                ['2', 'Atur pencarian', 'Pilih kelurahan dan harga yang sesuai.'],
                ['3', 'Lihat informasi',    'Periksa informasi yang tersedia sebelum menghubungi depot.'],
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

@extends('layouts.app')

@section('title', 'Jelajahi Depot - Watr.id')
@section('hide_navbar', '1')

@section('content')
<section class="relative h-[100dvh] md:h-screen">

    {{-- Peta --}}
    <div id="map" class="absolute inset-0 z-0"
         data-batas-kecamatan-url="{{ asset('data/administrasi/batas_kecamatan_bogor_tengah.geojson') }}"
         data-batas-kelurahan-url="{{ asset('data/administrasi/batas_kelurahan_bogor_tengah.geojson') }}"
         data-sungai-url="{{ asset('data/hidrologi/sungai_bogor_tengah.geojson') }}"
         data-faskes-url="{{ asset('data/faskes/fasilitas_kesehatan_bogor_tengah.geojson') }}"></div>

    {{-- Kanan atas: Favorit + Halaman Utama --}}
<div class="absolute right-4 top-4 z-1000 flex flex-col items-end gap-1 sm:flex-row sm:items-center sm:gap-3">
    <button id="fav-btn" type="button"
            class="flex min-h-11 items-center gap-1.5 rounded-full border border-brand-blue/40 bg-white px-3 py-2
                   text-xs font-medium text-brand-green-3 shadow-lg transition hover:bg-slate-50 sm:min-h-0 sm:gap-2 sm:px-5 sm:py-2.5 sm:text-sm">
        <svg class="size-4 text-brand-blue" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path stroke-linejoin="round" d="M12 21s-7.5-4.6-9.6-9.2C.9 8.4 2.7 5 6 5c2 0 3.3 1.1 4 2.2h4C14.7 6.1 16 5 18 5c3.3 0 5.1 3.4 3.6 6.8C19.5 16.4 12 21 12 21z"/>
        </svg>
        Favorit
        <span id="fav-count"
              class="flex size-5 items-center justify-center rounded-full bg-sky-100 text-[11px] font-semibold text-brand-blue">0</span>
    </button>

    <a href="{{ route('home') }}"
       class="btn-popup flex min-h-11 items-center gap-1.5 rounded-full bg-brand-blue px-3 py-2 text-xs font-medium
              text-white shadow-lg transition hover:brightness-95 sm:min-h-0 sm:gap-2 sm:px-5 sm:py-2.5 sm:text-sm">
        <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 12H5M12 19l-7-7 7-7"/>
        </svg>
        Halaman Utama
    </a>
</div>

{{-- Panel Depot favorit --}}
<div id="fav-panel"
     class="absolute right-4 top-20 z-1000 hidden max-h-[calc(100dvh-8rem)] w-[calc(100%-2rem)] max-w-sm
            overflow-y-auto rounded-2xl bg-white p-4 shadow-xl sm:p-5 md:max-h-[calc(100%-7rem)]">
    <div class="flex items-start justify-between">
        <div>
            <h2 class="text-lg font-semibold text-brand-green-3">Depot favorit</h2>
            <p id="fav-jumlah" class="mt-1 text-xs text-slate-500">0 depot tersimpan</p>
        </div>
        <button id="fav-close" type="button" aria-label="Tutup"
                class="flex size-11 items-center justify-center rounded-full bg-slate-100 text-slate-600 transition hover:bg-slate-200 md:size-9">
            <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/>
            </svg>
        </button>
    </div>

    <ul id="fav-list" class="mt-4 space-y-3 border-t border-slate-100 pt-4"></ul>
</div>

    {{-- Tombol buka panel (HP) --}}
    <button id="panel-toggle" type="button"
            class="absolute left-4 top-4 z-1000 flex min-h-11 items-center rounded-full bg-white px-4 py-2 text-xs font-medium shadow-lg md:hidden">
        Filter
    </button>

    {{-- Kolom kiri atas: cari + filter / detail --}}
    <div id="panel"
         class="absolute inset-x-4 top-28 bottom-24 z-1000 hidden flex max-h-none w-[calc(100%-5rem)] max-w-none
                flex-col gap-3 md:inset-x-auto md:bottom-auto md:left-4 md:right-auto md:top-4
                md:max-h-[calc(100%-6rem)] md:w-80 md:max-w-xs">

        {{-- Pil cari --}}
        <div class="flex items-center gap-2 rounded-full bg-white px-4 py-2.5 shadow-lg">
            <svg class="size-5 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <circle cx="11" cy="11" r="7" stroke-width="2"/>
                <path stroke-linecap="round" stroke-width="2" d="M21 21l-4.3-4.3"/>
            </svg>
            <input id="f-q" type="text" placeholder="Cari nama depot, alamat, atau kelurahan..."
                   class="w-full bg-transparent text-sm outline-none placeholder:text-slate-400">
        </div>

        {{-- Kartu filter --}}
        <div id="filter-card" class="min-h-0 flex-1 overflow-y-auto rounded-2xl bg-white p-4 shadow-xl sm:p-5 md:flex-none">

            {{-- Kelurahan --}}
            <label class="block text-sm font-medium text-brand-green-3">Kelurahan</label>
            <select id="f-kel" class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm outline-none">
                <option value="">Semua kelurahan</option>
            </select>

            {{-- Harga --}}
            <div class="mt-5 flex items-baseline justify-between">
                <label class="text-sm font-medium text-brand-green-3">
                    Harga maksimum <span class="text-xs font-normal text-slate-500">(per galon)</span>
                </label>
                <span id="harga-label" class="text-xs font-semibold text-brand-blue">Rp15.000</span>
            </div>
            <input id="f-harga" type="range" min="0" max="15000" step="500" value="15000"
                   class="mt-2 w-full accent-brand-blue">
            <div class="flex justify-between text-[10px] text-slate-400">
                <span>Rp0</span><span>Rp15.000</span>
            </div>

            {{-- Layanan --}}
            <p class="mt-5 text-sm font-medium text-brand-green-3">Layanan</p>
            <div class="mt-2 grid grid-cols-2 gap-2">
                <label class="flex cursor-pointer items-center gap-2 rounded-xl border border-slate-200 px-3 py-2.5 text-xs text-slate-700">
                    <input id="f-antar" type="checkbox" class="size-4 shrink-0 accent-brand-blue">
                    Layanan antar
                </label>
                <label class="flex cursor-pointer items-center gap-2 rounded-xl border border-slate-200 px-3 py-2.5 text-xs text-slate-700">
                    <input id="f-harian" type="checkbox" class="size-4 shrink-0 accent-brand-blue">
                    Buka setiap hari
                </label>
            </div>

            {{-- Terapkan filter --}}
            <button id="f-apply" type="button"
                    class="mt-6 min-h-11 w-full rounded-full bg-brand-blue py-2.5 text-sm font-medium text-white transition hover:brightness-95">
                Terapkan filter
            </button>

            {{-- Hasil (disembunyikan sampai filter diterapkan) --}}
            <div id="hasil-wrap" class="hidden">
                <p class="mt-6 text-sm font-medium text-brand-green-3">
                    Hasil pencarian <span id="hasil-jumlah" class="text-xs font-normal text-slate-500"></span>
                </p>
                <ul id="hasil" class="mt-3 space-y-3"></ul>

                <button id="f-reset" type="button"
                        class="mt-6 w-full rounded-full border border-brand-blue bg-white py-2.5 text-sm font-medium
                               text-brand-blue transition hover:bg-brand-blue/10">
                    Reset filter
                </button>
            </div>
        </div>

        {{-- Kartu detail depot (diisi lewat JS) --}}
        <div id="detail-card" class="hidden min-h-0 overflow-y-auto rounded-2xl bg-white p-4 shadow-xl"></div>
    </div>

    {{-- Layer peta + Legenda (kiri bawah) --}}
    <div class="absolute bottom-4 left-4 z-1001 flex max-h-[calc(100dvh-2rem)] flex-col-reverse items-start gap-3 overflow-y-auto md:max-h-none md:flex-row md:items-end md:overflow-visible">

        <div class="flex flex-col items-start gap-2">

            {{-- Panel Layer peta --}}
            <div id="layer-panel" class="hidden w-60 rounded-xl bg-white p-4 shadow-xl md:w-64">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2 text-sm font-semibold text-brand-green-3">
                        <svg class="size-4 text-brand-blue" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linejoin="round" d="M12 3l9 5-9 5-9-5 9-5z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13l9 5 9-5"/>
                        </svg>
                        Pengaturan peta
                    </div>
                    <button id="layer-close" type="button" aria-label="Tutup" class="text-slate-400 hover:text-slate-600">
                        <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/>
                        </svg>
                    </button>
                </div>

                <div class="mt-4 space-y-3 text-xs text-slate-700">
                    @foreach ([
                        ['depot', 'Lokasi depot', true],
                        ['kesehatan', 'Fasilitas kesehatan', true],
                        ['sungai', 'Sungai', false],
                        ['kecamatan', 'Batas kecamatan Bogor Tengah', true],
                        ['kelurahan', 'Batas kelurahan', true],
                    ] as [$key, $label, $on])
                        <label class="flex cursor-pointer items-center gap-2.5">
                            <input type="checkbox" data-ov="{{ $key }}" class="ov-check size-4 accent-brand-blue" @checked($on)>
                            {{ $label }}
                        </label>
                    @endforeach
                </div>
                <p id="layer-load-error" class="hidden mt-3 rounded-md bg-red-50 p-2 text-[11px] leading-relaxed text-red-700" role="alert"></p>

                <div class="mt-4 border-t border-slate-200 pt-3">
                    <p class="text-[11px] text-slate-500">Peta dasar</p>
                    <div class="mt-2 grid grid-cols-2 gap-1 rounded-lg bg-slate-100 p-1 text-xs">
                        <button type="button" data-base="jalan"
                                class="base-opt rounded-md bg-sky-100 py-1.5 font-medium text-brand-blue">Jalan</button>
                        <button type="button" data-base="satelit"
                                class="base-opt rounded-md py-1.5 font-medium text-slate-600">Satelit</button>
                    </div>
                </div>

                <button id="legend-open" type="button"
                        class="mt-3 flex w-full items-center justify-between border-t border-slate-200 pt-3 text-xs text-slate-700 hover:text-brand-blue">
                    Lihat legenda
                    <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 6l6 6-6 6"/>
                    </svg>
                </button>
            </div>

            {{-- Tombol layer --}}
            <button id="layer-btn" type="button" aria-label="Pengaturan peta"
                    class="flex size-11 items-center justify-center rounded-lg bg-white text-brand-blue shadow-lg transition hover:bg-slate-50 md:size-10">
                <svg class="size-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linejoin="round" d="M12 3l9 5-9 5-9-5 9-5z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 13l9 5 9-5"/>
                </svg>
            </button>
        </div>

        {{-- Panel Legenda (di kanan panel layer) --}}
        <div id="legend-panel" class="hidden w-[min(14rem,calc(100vw-5rem))] rounded-xl bg-white p-4 shadow-xl md:mb-12 md:w-56">
            <div class="flex items-center justify-between">
                <p class="text-sm font-semibold text-brand-green-3">Legenda</p>
                <button id="legend-close" type="button" aria-label="Tutup" class="text-slate-400 hover:text-slate-600">
                    <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/>
                    </svg>
                </button>
            </div>

            <ul class="mt-4 space-y-3 text-[11px] text-slate-600">
                <li class="flex items-center gap-3">
                    <svg class="size-4 shrink-0" viewBox="0 0 32 42"><path d="M16 0C7.2 0 0 7 0 15.7 0 27 16 42 16 42s16-15 16-26.3C32 7 24.8 0 16 0z" fill="#0ACBFE"/><circle cx="16" cy="15.5" r="6" fill="#fff"/></svg>
                    Depot air minum
                </li>
                <li class="flex items-center gap-3">
                    <span class="flex size-5 shrink-0 items-center justify-center rounded-full bg-[#0C9624] text-base font-bold leading-none text-white">+</span>
                    Fasilitas kesehatan
                </li>
                <li class="flex items-center gap-3">
                    <span class="h-0.5 w-4 shrink-0 bg-brand-blue"></span> Jaringan sungai
                </li>
                <li class="flex items-center gap-3">
                    <span class="h-0.5 w-4 shrink-0 bg-red-600"></span> Batas kecamatan Bogor Tengah
                </li>
                <li class="flex items-center gap-3">
                    <span class="h-px w-4 shrink-0 bg-slate-500"></span> Batas kelurahan
                </li>
            </ul>
        </div>
    </div>

    {{-- Kontrol kanan bawah: lokasi + zoom --}}
    <div class="absolute bottom-4 right-4 z-1000 flex flex-col gap-2">
        <button id="locate-btn" type="button" aria-label="Lokasi saya"
                class="flex size-11 items-center justify-center rounded-lg bg-white text-slate-700 shadow-lg transition hover:bg-slate-50 md:size-10">
            <svg class="size-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="3"/><circle cx="12" cy="12" r="7"/>
                <path stroke-linecap="round" d="M12 2v3M12 19v3M2 12h3M19 12h3"/>
            </svg>
        </button>
        <div class="flex flex-col overflow-hidden rounded-lg bg-white shadow-lg">
            <button id="zoom-in" type="button" aria-label="Perbesar"
                    class="flex size-11 items-center justify-center text-xl text-slate-700 transition hover:bg-slate-50 md:size-10">+</button>
            <button id="zoom-out" type="button" aria-label="Perkecil"
                    class="flex size-11 items-center justify-center border-t border-slate-200 text-xl text-slate-700 transition hover:bg-slate-50 md:size-10">&minus;</button>
        </div>
    </div>
</section>
@endsection

@push('scripts')
    <script>window.DEPOTS = @json($depots);</script>
    @vite('resources/js/jelajahi.js')
@endpush

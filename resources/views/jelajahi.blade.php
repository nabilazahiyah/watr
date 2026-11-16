@extends('layouts.app')

@section('title', 'Jelajahi Depot - Watr.id')
@section('navbar_solid', '1')

@section('content')
<section class="relative h-screen pt-20">

    {{-- Peta --}}
    <div id="map" class="absolute inset-x-0 bottom-0 top-20 z-0"></div>

    {{-- Tombol buka panel (HP) --}}
    <button id="panel-toggle" type="button"
            class="absolute left-4 top-24 z-1000 rounded-full bg-white px-4 py-2 text-xs font-medium shadow-lg md:hidden">
        Filter
    </button>

    {{-- Panel filter --}}
    <aside id="panel"
           class="absolute bottom-4 left-4 top-24 z-1000 hidden w-[calc(100%-2rem)] max-w-xs overflow-y-auto
                  rounded-2xl bg-white p-5 shadow-xl md:top-28 md:block md:w-80">

        {{-- Cari --}}
        <div class="flex items-center gap-2 rounded-xl border border-slate-200 px-3 py-2.5">
            <svg class="size-5 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <circle cx="11" cy="11" r="7" stroke-width="2"/>
                <path stroke-linecap="round" stroke-width="2" d="M21 21l-4.3-4.3"/>
            </svg>
            <input id="f-q" type="text" placeholder="Cari depot atau lokasi..."
                   class="w-full bg-transparent text-sm outline-none placeholder:text-slate-400">
        </div>

        {{-- Kelurahan --}}
        <label class="mt-5 block text-sm font-medium text-brand-green-3">Kelurahan</label>
        <select id="f-kel" class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm outline-none">
            <option value="">Semua Kelurahan</option>
            @foreach ($kelurahan as $k)
                <option value="{{ $k }}">{{ $k }}</option>
            @endforeach
        </select>

        {{-- Harga --}}
        <div class="mt-5 flex items-baseline justify-between">
            <label class="text-sm font-medium text-brand-green-3">
                Rentang Harga <span class="text-xs font-normal text-slate-500">(per galon)</span>
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
        <label class="mt-2 flex items-center gap-2 text-xs text-slate-700">
            <input id="f-antar" type="checkbox" class="size-4 accent-brand-blue"> Antar jemput
        </label>
        <label class="mt-2 flex items-center gap-2 text-xs text-slate-700">
            <input id="f-24" type="checkbox" class="size-4 accent-brand-blue"> Buka 24 jam
        </label>

        <button id="f-apply" type="button"
                class="mt-6 w-full rounded-full bg-brand-blue py-2.5 text-sm font-medium text-white transition hover:brightness-95">
            Terapkan Filter
        </button>

        {{-- Hasil --}}
        <p class="mt-6 text-sm font-medium text-brand-green-3">
            Hasil Pencarian <span id="hasil-jumlah" class="text-xs font-normal text-slate-500"></span>
        </p>
        <ul id="hasil" class="mt-3 space-y-3"></ul>
    </aside>
</section>
@endsection

@push('scripts')
    <script>window.DEPOTS = @json($depots);</script>
    @vite('resources/js/jelajahi.js')
@endpush
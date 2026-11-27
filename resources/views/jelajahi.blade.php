@extends('layouts.app')

@section('title', 'Daftar Depot - Watr.id')
@section('navbar_solid', '1')

@section('content')

<section class="bg-sky-50 py-12 md:py-14">
    <div class="mx-auto flex max-w-6xl flex-col gap-6 px-6 md:flex-row md:items-center md:justify-between">
        <div>
            <nav class="text-[11px] text-slate-500">
                <a href="{{ route('home') }}" class="hover:text-brand-blue">Beranda</a> / Daftar depot
            </nav>
            <h1 class="mt-5 text-2xl font-semibold text-brand-green-3 md:text-3xl">Daftar depot</h1>
            <p class="mt-3 max-w-md text-xs leading-relaxed text-slate-600">
                Cari depot air minum di Bogor Tengah dan lihat informasi yang tersedia.
            </p>
        </div>

        <a href="{{ route('peta') }}"
           class="self-start rounded-full bg-brand-blue px-8 py-2.5 text-xs font-medium text-white transition hover:brightness-95 md:self-center">
            Lihat di peta
        </a>
    </div>
</section>

{{-- ================= FILTER + DAFTAR ================= --}}
<section class="bg-white py-8 md:py-10">
    <div class="mx-auto max-w-6xl px-6">

        {{-- Filter --}}
        <form id="jelajahi-filter-form" method="GET" action="{{ route('jelajahi') }}"
              class="rounded-2xl border border-slate-100 p-4 shadow-sm md:p-5">

            <div class="grid gap-3 md:grid-cols-[1fr_auto_auto_auto]">
                <div class="flex items-center gap-2 rounded-full border border-slate-200 px-4 py-2.5">
                    <svg class="size-4 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="7" stroke-width="2"/>
                        <path stroke-linecap="round" stroke-width="2" d="M21 21l-4.3-4.3"/>
                    </svg>
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama depot, alamat, atau kelurahan..."
                           class="w-full bg-transparent text-xs outline-none placeholder:text-slate-400">
                </div>

                <select name="kelurahan" class="rounded-full border border-slate-200 bg-white px-4 py-2.5 text-xs outline-none md:w-44">
                    <option value="">Semua kelurahan</option>
                    @foreach ($kelurahan as $k)
                        <option value="{{ $k }}" @selected(request('kelurahan') === $k)>{{ $k }}</option>
                    @endforeach
                </select>

                <select name="urut" class="rounded-full border border-slate-200 bg-white px-4 py-2.5 text-xs outline-none md:w-44">
                    <option value="nama_asc"  @selected(request('urut', 'nama_asc') === 'nama_asc')>Nama depot (A-Z)</option>
                    <option value="nama_desc" @selected(request('urut') === 'nama_desc')>Nama depot (Z-A)</option>
                    <option value="harga_asc" @selected(request('urut') === 'harga_asc')>Harga terendah</option>
                </select>

                <button type="submit"
                        class="rounded-full bg-brand-blue px-8 py-2.5 text-xs font-medium text-white transition hover:brightness-95">
                    Cari depot
                </button>
            </div>

            {{-- Filter layanan dan jadwal --}}
            <div class="mt-4 flex flex-wrap gap-2">
                @foreach ([['harian', 'Buka setiap hari'], ['antar', 'Layanan antar']] as [$val, $label])
                    <label class="cursor-pointer">
                        <input type="checkbox" name="{{ $val }}" value="1" class="peer sr-only"
                               @checked(request()->boolean($val))>
                        <span class="block rounded-full border border-brand-blue/40 bg-white px-4 py-1.5 text-[11px] text-slate-700 transition
                                     peer-checked:border-brand-blue peer-checked:bg-sky-100 peer-checked:text-brand-blue">
                            {{ $label }}
                        </span>
                    </label>
                @endforeach
            </div>

            <div class="mt-4 flex items-center justify-between gap-3">
                <a href="{{ route('jelajahi') }}" class="text-[11px] text-slate-500 transition hover:text-brand-blue">
                    Reset filter
                </a>
                <span class="text-[10px] text-slate-400">Hasil diperbarui otomatis saat pencarian atau filter berubah.</span>
            </div>
        </form>

        <div id="jelajahi-results">
            {{-- Judul daftar --}}
            <div class="mt-10 flex items-baseline justify-between">
                <h2 class="text-base font-semibold text-brand-green-3">Depot di Bogor Tengah</h2>
                <p class="text-[11px] text-slate-500" aria-live="polite">{{ count($depots) }} depot ditemukan</p>
            </div>

            {{-- Kartu --}}
            <div class="mt-5 grid gap-6 md:grid-cols-3">
                @forelse ($depots as $d)
                <article class="flex flex-col rounded-2xl border border-slate-100 bg-white p-3 shadow-md shadow-slate-300/40">

                    <div class="relative">
                        <img src="{{ $d['foto'] }}" alt="{{ $d['nama'] }}"
                             class="aspect-4/3 w-full rounded-xl object-cover">
                        <button type="button" aria-label="Tambahkan ke favorit" data-favorite-id="{{ $d['id'] }}"
                                class="fav-btn absolute right-2 top-2 flex size-8 items-center justify-center rounded-full bg-white/90 text-slate-500 shadow transition hover:scale-105">
                            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linejoin="round" d="M12 21s-7.5-4.6-9.6-9.2C.9 8.4 2.7 5 6 5c2 0 3.3 1.1 4 2.2h4C14.7 6.1 16 5 18 5c3.3 0 5.1 3.4 3.6 6.8C19.5 16.4 12 21 12 21z"/>
                            </svg>
                        </button>
                    </div>

                    <div class="flex flex-1 flex-col px-1 pb-1 pt-3">
                        <h3 class="text-sm font-semibold text-brand-green-3">{{ $d['nama'] }}</h3>
                        <p class="mt-2 text-[11px] text-slate-500">DAMIU · {{ $d['kelurahan'] }}</p>

                        {{-- Harga --}}
                        @if ($d['harga_min'])
                            <p class="mt-3 text-sm font-semibold text-brand-green-3">
                                Rp{{ number_format($d['harga_min'], 0, ',', '.') }} – Rp{{ number_format($d['harga_max'], 0, ',', '.') }}
                            </p>
                            <p class="text-[10px] text-slate-500">per galon</p>
                        @else
                            <p class="text-[10px] text-slate-500">Harga belum tersedia</p>
                        @endif

                        {{-- Jam & layanan --}}
                        <p class="mt-2 text-[11px] text-slate-600">
                            Jam buka {{ substr($d['jam_buka'], 0, 5) }}–{{ substr($d['jam_tutup'], 0, 5) }}
                            · {{ $d['buka_setiap_hari'] ? 'Buka setiap hari' : 'Tidak buka setiap hari' }}
                        </p>
                        <p class="mt-1 text-[11px] text-slate-500">
                            {{ count($d['hari_libur']) ? 'Hari libur: '.implode(', ', $d['hari_libur']) : 'Jadwal hari libur belum tersedia' }}
                        </p>
                        <p class="mt-1 text-[11px] text-slate-600">
                            {{ $d['antar'] ? 'Melayani antar' : 'Tidak melayani antar' }}
                        </p>

                        <a href="{{ route('peta', ['depot' => $d['id']]) }}"
                           class="mt-4 block rounded-full border border-slate-300 py-2 text-center text-[11px] font-medium text-slate-700
                                  transition hover:border-brand-blue hover:text-brand-blue">
                            Lihat informasi depot →
                        </a>
                    </div>
                </article>
                @empty
                    <p class="col-span-full py-10 text-center text-sm text-slate-500">
                        Tidak ditemukan depot yang cocok. Coba ubah pencarian atau filter.
                    </p>
                @endforelse
            </div>
        </div>
    </div>

    
</section>

{{-- ================= CTA PETA ================= --}}
<section class="bg-white pb-16 md:pb-20">
    <div class="mx-auto max-w-6xl px-6">
        <div class="flex flex-col gap-8 rounded-3xl bg-brand-sky p-8 md:flex-row md:items-center md:justify-between md:px-12 md:py-10">

            <div class="max-w-xs">
                <h2 class="text-xl font-semibold leading-snug text-brand-green-3 md:text-2xl">
                    Lebih nyaman mencari lewat peta?
                </h2>
                <p class="mt-3 text-xs leading-relaxed text-slate-600">
                    Lihat persebaran depot dan informasi di sekitarnya melalui peta interaktif WATR.
                </p>
                <a href="{{ route('peta') }}"
                   class="mt-6 inline-block rounded-full bg-brand-blue px-7 py-2.5 text-xs font-medium text-white transition hover:brightness-95">
                    Buka peta interaktif
                </a>
            </div>

            <a href="{{ route('peta') }}" class="block w-full md:w-[46%]" aria-label="Buka peta interaktif">
                <img src="{{ asset('images/map-preview.png') }}" alt="Pratinjau peta depot"
                     class="aspect-2/1 w-full rounded-2xl object-cover shadow-sm">
            </a>
        </div>
    </div>
</section>

@endsection


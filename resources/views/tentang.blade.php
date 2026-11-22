@extends('layouts.app')

@section('title', 'Tentang - Watr.id')
@section('navbar_solid', '1')

@section('content')

<section class="bg-sky-50 py-16 md:py-20">
    <div class="mx-auto grid max-w-6xl items-center gap-12 px-6 md:grid-cols-2">

        {{-- Teks --}}
        <div>
            <p class="text-[11px] font-semibold uppercase tracking-wide text-sky-600">Tentang WATR</p>

            <h1 class="mt-4 text-3xl font-semibold leading-tight text-slate-800 md:text-4xl">
                Lebih dekat dengan<br>pilihan air minummu.
            </h1>

            <p class="mt-6 max-w-md text-sm leading-relaxed text-slate-600">
                WATR adalah website informasi dan peta interaktif Depot Air Minum Isi Ulang
                (DAMIU) di Kecamatan Bogor Tengah, Kota Bogor.
            </p>
            <p class="mt-5 max-w-md text-sm leading-relaxed text-slate-600">
                Dibangun untuk warga, pendatang, dan siapa pun yang membutuhkan akses cepat
                ke informasi depot air minum di pusat Kota Bogor.
            </p>

            <div class="mt-8 flex flex-wrap gap-3">
                <a href="{{ route('jelajahi') }}"
                   class="rounded-full bg-brand-blue px-6 py-2.5 text-xs font-medium text-white transition hover:brightness-95">
                    Jelajahi peta
                </a>
                <a href="{{ route('jelajahi') }}"
                   class="rounded-full border border-slate-200 bg-white px-6 py-2.5 text-xs font-medium text-slate-800 transition hover:bg-slate-50">
                    Lihat semua depot
                </a>
            </div>
        </div>

        {{-- Kolase foto --}}
        <div class="flex justify-center md:justify-end">
            <img src="{{ asset('images/about-collage.png') }}" alt="Depot air minum isi ulang"
                 class="w-full max-w-md">
        </div>
    </div>
</section>

{{-- ================= KENAPA WATR HADIR ================= --}}
<section class="bg-white py-16 md:py-20">
    <div class="mx-auto grid max-w-6xl gap-8 px-6 md:grid-cols-2 md:gap-12">

        <div>
            <p class="text-[11px] font-semibold uppercase tracking-wide text-sky-600">Kenapa WATR hadir?</p>
            <h2 class="mt-4 text-2xl font-semibold leading-snug text-slate-800 md:text-3xl">
                Informasi depot,<br>dalam satu tempat.
            </h2>
        </div>

        <div class="space-y-4 text-sm leading-relaxed text-slate-600">
            <p>
                Mencari depot air minum isi ulang seharusnya tidak rumit. WATR membantu
                mempertemukan kebutuhan sehari-hari dengan informasi lokasi dan layanan
                depot di sekitar.
            </p>
            <p>
                Mulai dari menemukan lokasi, melihat informasi harga dan jam operasional,
                sampai menyimpan depot pilihan&mdash;semuanya dapat dijelajahi melalui satu
                website.
            </p>
        </div>
    </div>
</section>

{{-- ================= YANG BISA KAMU LAKUKAN ================= --}}
<section class="bg-brand-sky py-16 md:py-20">
    <div class="mx-auto max-w-6xl px-6">
        <p class="text-[11px] font-semibold uppercase tracking-wide text-sky-600">Yang bisa kamu lakukan</p>
        <h2 class="mt-4 text-2xl font-semibold leading-snug text-slate-800 md:text-3xl">
            Satu website, lebih mudah memilih depot.
        </h2>

        <div class="mt-8 grid gap-5 md:grid-cols-3">
            @foreach ([
                ['Temukan lokasi depot', 'Jelajahi persebaran depot melalui peta interaktif. Gunakan pencarian dan filter untuk menemukan lokasi yang sesuai.',
                    '<circle cx="12" cy="12" r="3"/><path stroke-linecap="round" d="M12 2v3M12 19v3M2 12h3M19 12h3"/>'],
                ['Kenali harga dan layanan', 'Lihat harga galon, rentang harga, jam operasional, serta ketersediaan layanan antar sebelum menghubungi depot.',
                    '<path stroke-linejoin="round" d="M12 3l9 5-9 5-9-5 9-5zM3 13l9 5 9-5"/>'],
                ['Simpan depot pilihan', 'Tandai depot sebagai favorit agar lebih mudah ditemukan kembali saat kamu membutuhkan isi ulang berikutnya.',
                    '<path stroke-linejoin="round" d="M12 21s-7.5-4.6-9.6-9.2C.9 8.4 2.7 5 6 5c2 0 3.3 1.1 4 2.2h4C14.7 6.1 16 5 18 5c3.3 0 5.1 3.4 3.6 6.8C19.5 16.4 12 21 12 21z"/>'],
            ] as [$judul, $desk, $ikon])
                <article class="rounded-xl bg-white p-5 shadow-md shadow-slate-300/40">
                    <span class="flex size-8 items-center justify-center rounded-lg bg-sky-100 text-sky-600">
                        <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">{!! $ikon !!}</svg>
                    </span>
                    <h3 class="mt-4 text-sm font-semibold text-slate-800">{{ $judul }}</h3>
                    <p class="mt-2 text-xs leading-relaxed text-slate-500">{{ $desk }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>

{{-- ================= PENTING DIKETAHUI ================= --}}
<section class="bg-white py-16 md:py-20">
    <div class="mx-auto grid max-w-6xl gap-10 px-6 md:grid-cols-2 md:gap-12">

        <div>
            <p class="text-[11px] font-semibold uppercase tracking-wide text-sky-600">Penting diketahui</p>
            <h2 class="mt-4 text-2xl font-semibold leading-snug text-slate-800 md:text-3xl">
                Informasi yang jelas.<br>Pilihan tetap di tanganmu.
            </h2>
            <p class="mt-5 max-w-sm text-sm leading-relaxed text-slate-600">
                WATR membantu pencarian informasi. Pastikan kebutuhanmu sesuai dengan layanan
                depot sebelum melakukan pemesanan.
            </p>
            <p class="mt-6 text-xs text-sky-600">Ada informasi yang perlu diperbarui?</p>
            <a href="mailto:watr.id@gmail.com" class="text-xs text-slate-600 hover:text-sky-600">watr.id@gmail.com</a>
        </div>

        <div class="space-y-3">
            @foreach ([
                ['Harga dan jam buka depot berubah', 'Konfirmasikan harga, status operasional, dan ketersediaan layanan antar langsung kepada depot.'],
                ['Lokasi membantu pencarian, bukan jaminan mutu', 'Informasi peta dan konteks lingkungan bukan pengganti pemeriksaan sendiri atau hasil uji kualitas air.'],
                ['Gunakan informasi secara bijak', 'Pilih depot sesuai kebutuhanmu dan sampaikan koreksi apabila menemukan informasi yang tidak sesuai.'],
            ] as [$judul, $desk])
                <div class="rounded-lg bg-sky-50 px-5 py-4">
                    <h3 class="text-xs font-semibold text-slate-800">{{ $judul }}</h3>
                    <p class="mt-1.5 text-[11px] leading-relaxed text-slate-500">{{ $desk }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ================= CTA ================= --}}
<section class="bg-white pb-16 md:pb-20">
    <div class="mx-auto max-w-6xl px-6">
        <div class="flex flex-col gap-6 rounded-2xl bg-brand-sky px-8 py-8 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-slate-800 md:text-2xl">Mulai kenali depot di sekitarmu.</h2>
                <p class="mt-2 text-xs text-slate-600">Buka peta atau telusuri daftar depot di Bogor Tengah.</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('peta') }}"
                   class="rounded-full bg-brand-blue px-6 py-2.5 text-xs font-medium text-white transition hover:brightness-95">
                    Jelajahi peta
                </a>
                <a href="{{ route('jelajahi') }}"
                   class="rounded-full border border-slate-200 bg-white px-6 py-2.5 text-xs font-medium text-slate-800 transition hover:bg-slate-50">
                    Lihat semua depot
                </a>
            </div>
        </div>
    </div>
</section>

@endsection
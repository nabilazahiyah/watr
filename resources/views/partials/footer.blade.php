<footer class="bg-brand-sky">
    <div class="mx-auto max-w-6xl px-6 pb-8 pt-14">

        <div class="grid gap-10 lg:grid-cols-[1.4fr_1fr_1fr_1.4fr]">

            {{-- Logo + deskripsi --}}
            <div>
                <a href="#beranda">
                    <img src="{{ asset('images/logo.svg') }}" alt="Watr.id" class="h-10">
                </a>
                <p class="mt-4 max-w-[15rem] text-xs leading-relaxed text-brand-green-3">
                    WATR menampilkan peta dan informasi depot air minum isi ulang di Bogor Tengah.
                </p>
            </div>

            {{-- Navigasi --}}
            <div>
                <h4 class="text-sm font-semibold text-brand-green-3">Navigasi</h4>
                <ul class="mt-4 space-y-3 text-xs text-brand-green-3">
                    <li><a href="#beranda" class="transition hover:text-brand-green-2">Beranda</a></li>
                    <li><a href="#jelajahi" class="transition hover:text-brand-green-2">Peta Interaktif</a></li>
                    <li><a href="#favorit" class="transition hover:text-brand-green-2">Daftar Favorit</a></li>
                    <li><a href={{ route('tentang') }} class="transition hover:text-brand-green-2">Tentang</a></li>
                </ul>
            </div>

            {{-- Fitur --}}
            <div>
                <h4 class="text-sm font-semibold text-brand-green-3">Fitur</h4>
                <ul class="mt-4 space-y-3 text-xs text-brand-green-3">
                    <li>Peta depot</li>
                    <li>Informasi depot</li>
                    <li>Depot favorit</li>
                    <li>Pencarian dan filter</li>
                </ul>
            </div>

            {{-- Kontak --}}
            <div>
                <h4 class="text-sm font-semibold text-brand-green-3">Kontak</h4>
                <ul class="mt-4 space-y-3 text-xs text-brand-green-3">
                    <li>Alamat: Bogor Tengah, Kota Bogor</li>
                    <li>Email: <a href="mailto:watr.id@gmail.com" class="transition hover:text-brand-green-2">watr.id@gmail.com</a></li>
                </ul>
            </div>

        </div>

        {{-- Garis + copyright --}}
        <div class="mt-12 border-t border-slate-300/70 pt-8 text-center text-xs text-brand-green-3">
            &copy; {{ date('Y') }} WATR. Hak cipta dilindungi.
        </div>

    </div>
</footer>

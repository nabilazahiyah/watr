document.getElementById('menu-btn')?.addEventListener('click', () => {
    document.getElementById('mobile-menu').classList.toggle('hidden');
});
// Filter kartu depot
const filterBtns = document.querySelectorAll('.filter-btn');
const depotCards = document.querySelectorAll('.depot-card');

filterBtns.forEach((btn) => {
    btn.addEventListener('click', () => {
        const f = btn.dataset.filter;

        filterBtns.forEach((b) => {
            const aktif = b === btn;
            b.classList.toggle('bg-brand-blue', aktif);
            b.classList.toggle('text-white', aktif);
            b.classList.toggle('bg-white', !aktif);
            b.classList.toggle('text-brand-blue', !aktif);
        });

        depotCards.forEach((card) => {
            const tampil = f === 'semua' || card.dataset[f] === '1';
            card.classList.toggle('hidden', !tampil);
        });
    });
});

import { initializeFavoriteButtons, syncFavoriteButtons } from './favorites';

initializeFavoriteButtons();

// Jelajahi: perbarui hasil otomatis tanpa meninggalkan halaman.
const jelajahiForm = document.getElementById('jelajahi-filter-form');

if (jelajahiForm) {
    const searchInput = jelajahiForm.querySelector('input[name="q"]');
    let searchTimer;
    let activeRequest;

    const applyJelajahiFilters = async () => {
        const url = new URL(jelajahiForm.action, window.location.href);
        url.search = new URLSearchParams(new FormData(jelajahiForm)).toString();

        activeRequest?.abort();
        activeRequest = new AbortController();

        try {
            const response = await fetch(url, {
                headers: { Accept: 'text/html', 'X-Requested-With': 'XMLHttpRequest' },
                signal: activeRequest.signal,
            });

            if (!response.ok) throw new Error(`HTTP ${response.status}`);

            const documentBaru = new DOMParser().parseFromString(await response.text(), 'text/html');
            const hasilBaru = documentBaru.getElementById('jelajahi-results');
            const hasilSaatIni = document.getElementById('jelajahi-results');

            if (!hasilBaru || !hasilSaatIni) throw new Error('Bagian hasil depot tidak ditemukan.');

            hasilSaatIni.replaceWith(document.importNode(hasilBaru, true));
            window.history.replaceState({}, '', `${url.pathname}${url.search}`);
            syncFavoriteButtons(document.getElementById('jelajahi-results'));
        } catch (error) {
            if (error.name === 'AbortError') return;

            // Tetap gunakan pengiriman form biasa jika permintaan dinamis gagal.
            window.location.assign(url);
        }
    };

    const applyImmediately = () => {
        window.clearTimeout(searchTimer);
        applyJelajahiFilters();
    };

    jelajahiForm.addEventListener('submit', (event) => {
        event.preventDefault();
        applyImmediately();
    });

    searchInput?.addEventListener('input', () => {
        window.clearTimeout(searchTimer);
        searchTimer = window.setTimeout(applyJelajahiFilters, 300);
    });

    jelajahiForm.querySelectorAll('select, input[type="checkbox"]').forEach((control) => {
        control.addEventListener('change', applyImmediately);
    });
}

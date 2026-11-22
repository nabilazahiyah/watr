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

import { isFav, toggleFav } from './favorit';

document.querySelectorAll('.fav-btn').forEach((btn) => {
    const id = Number(btn.dataset.id);

    if (id) btn.setAttribute('aria-pressed', String(isFav(id)));

    btn.addEventListener('click', () => {
        const on = id
            ? toggleFav(id)
            : btn.getAttribute('aria-pressed') !== 'true'; // kartu tanpa data-id: hanya tampilan
        btn.setAttribute('aria-pressed', String(on));
    });
});
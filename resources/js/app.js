import { initializeFavoriteButtons } from './favorites';

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

initializeFavoriteButtons();

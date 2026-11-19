const STORAGE_KEY = 'watr:favorites:v1';

const readFavorites = () => {
    try {
        const stored = JSON.parse(window.localStorage.getItem(STORAGE_KEY) ?? '[]');

        return new Set(Array.isArray(stored)
            ? stored.filter((id) => typeof id === 'string' || typeof id === 'number').map(String)
            : []);
    } catch {
        return null;
    }
};

let favorites = readFavorites() ?? new Set();

export function isFavorite(id) {
    return favorites.has(String(id));
}

export function syncFavoriteButtons(root = document) {
    favorites = readFavorites() ?? favorites;

    root.querySelectorAll('[data-favorite-id]').forEach((button) => {
        const active = isFavorite(button.dataset.favoriteId);
        button.classList.toggle('text-red-500', active);
        button.classList.toggle('text-white/80', !active);
        button.setAttribute('aria-pressed', String(active));
        button.setAttribute('aria-label', active ? 'Hapus dari favorit' : 'Tambahkan ke favorit');
    });
}

function toggleFavorite(id) {
    const key = String(id);

    if (favorites.has(key)) {
        favorites.delete(key);
    } else {
        favorites.add(key);
    }

    try {
        window.localStorage.setItem(STORAGE_KEY, JSON.stringify([...favorites]));
    } catch {
        // Keep the favorite available for the current page if storage is unavailable.
    }

    syncFavoriteButtons();
}

export function initializeFavoriteButtons() {
    syncFavoriteButtons();

    document.addEventListener('click', (event) => {
        const button = event.target.closest('[data-favorite-id]');

        if (!button) return;

        event.preventDefault();
        event.stopPropagation();
        toggleFavorite(button.dataset.favoriteId);
    });

    window.addEventListener('storage', (event) => {
        if (event.key !== STORAGE_KEY) return;

        favorites = readFavorites() ?? favorites;
        syncFavoriteButtons();
    });
}

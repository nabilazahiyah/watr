const STORAGE_KEY = 'watr:favorites:v1';
const LEGACY_STORAGE_KEY = 'watr:favorit';

const normalizeIds = (ids) => [...new Set(ids
    .filter((id) => typeof id === 'string' || typeof id === 'number')
    .map(String)
    .filter((id) => id !== '' && id !== '0'))];

const readFavorites = () => {
    try {
        const current = window.localStorage.getItem(STORAGE_KEY);
        const legacy = window.localStorage.getItem(LEGACY_STORAGE_KEY);
        const stored = JSON.parse(current ?? legacy ?? '[]');
        const ids = normalizeIds(Array.isArray(stored) ? stored : []);

        if (current === null && legacy !== null) {
            window.localStorage.setItem(STORAGE_KEY, JSON.stringify(ids));
        }

        return new Set(ids);
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
        button.style.color = active ? '#ef4444' : '';
        button.setAttribute('aria-pressed', String(active));
        button.setAttribute('aria-label', active ? 'Hapus dari favorit' : 'Tambahkan ke favorit');
    });
}

export function getFavoriteIds() {
    return [...favorites];
}

export function toggleFavorite(id) {
    const key = String(id);
    const active = !favorites.has(key);

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
    window.dispatchEvent(new CustomEvent('watr:favorites:change'));
    return active;
}

export function initializeFavoriteButtons() {
    syncFavoriteButtons();

    document.addEventListener('click', (event) => {
        const button = event.target.closest?.('[data-favorite-id]');

        if (!button) return;

        event.preventDefault();
        event.stopPropagation();
        toggleFavorite(button.dataset.favoriteId);
    });

    window.addEventListener('storage', (event) => {
        if (event.key !== STORAGE_KEY && event.key !== LEGACY_STORAGE_KEY) return;

        favorites = readFavorites() ?? favorites;
        syncFavoriteButtons();
        window.dispatchEvent(new CustomEvent('watr:favorites:change'));
    });
}

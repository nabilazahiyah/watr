// Compatibility adapter for older imports; storage is owned by favorites.js.
import { getFavoriteIds, isFavorite, toggleFavorite } from './favorites';

export const getFav = () => getFavoriteIds().map(Number);
export const isFav = isFavorite;
export const toggleFav = toggleFavorite;

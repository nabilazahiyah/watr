const KEY = 'watr:favorit';

export const getFav = () => {
    try { return JSON.parse(localStorage.getItem(KEY)) ?? []; }
    catch { return []; }
};

export const isFav = (id) => getFav().includes(Number(id));

export function toggleFav(id) {
    id = Number(id);
    const list = getFav();
    const ada = list.includes(id);
    const baru = ada ? list.filter((x) => x !== id) : [...list, id];

    try { localStorage.setItem(KEY, JSON.stringify(baru)); } catch {}
    window.dispatchEvent(new CustomEvent('favorit:change'));
    return !ada;
}
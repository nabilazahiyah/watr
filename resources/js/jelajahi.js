import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import { syncFavoriteButtons } from './favorites';

const depots = Array.isArray(window.DEPOTS) ? window.DEPOTS : [];
const depotPlaceholder = window.DEPOT_PLACEHOLDER || '/images/fotodummy.jpg';

const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (character) => ({
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#039;',
}[character]));

const formatPrice = (value) => `Rp${Number(value).toLocaleString('id-ID')}`;
const formatTime = (value) => value ? String(value).slice(0, 5) : 'Tidak tersedia';

const safeMapsUrl = (value) => {
    try {
        const url = new URL(value);
        const allowedHosts = ['maps.google.com', 'www.google.com', 'google.com', 'maps.app.goo.gl'];

        return url.protocol === 'https:' && allowedHosts.includes(url.hostname.toLowerCase())
            ? url.href
            : null;
    } catch {
        return null;
    }
};

// ---------- Peta ----------
const map = L.map('map', { zoomControl: false }).setView([-6.5985, 106.7975], 14);
L.control.zoom({ position: 'bottomright' }).addTo(map);

L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '&copy; OpenStreetMap contributors',
}).addTo(map);

const pinIcon = L.divIcon({
    className: 'pin-depot',
    html: `<svg width="32" height="42" viewBox="0 0 32 42">
             <path d="M16 0C7.2 0 0 7 0 15.7 0 27 16 42 16 42s16-15 16-26.3C32 7 24.8 0 16 0z" fill="#0ACBFE"/>
             <circle cx="16" cy="15.5" r="6" fill="#fff"/>
           </svg>`,
    iconSize: [32, 42],
    iconAnchor: [16, 42],
    popupAnchor: [0, -40],
});

const layer = L.layerGroup().addTo(map);
const markers = new Map();

map.on('popupopen', (event) => {
    syncFavoriteButtons(event.popup.getElement());
});

// ---------- Popup ----------
const popupHtml = (depot) => {
    const mapUrl = safeMapsUrl(depot.link_google_maps);
    const hariLibur = Array.isArray(depot.hari_libur) ? depot.hari_libur : [];
    const mapAction = mapUrl
        ? `<a href="${escapeHtml(mapUrl)}" target="_blank" rel="noopener noreferrer" class="mt-3 block rounded-lg border border-slate-300 py-2 text-center text-xs font-medium text-slate-700 transition hover:bg-slate-50">Buka Google Maps</a>`
        : '<p class="mt-3 text-xs text-slate-500">Link peta belum tersedia.</p>';

    return `
      <div>
        <div class="relative h-32 overflow-hidden rounded-t-xl">
          <img src="${escapeHtml(depotPlaceholder)}" alt="Foto simulasi ${escapeHtml(depot.nama)}" class="h-full w-full object-cover">
          <button type="button" data-favorite-id="${escapeHtml(depot.id)}" aria-label="Tambahkan ke favorit" aria-pressed="false"
            class="popup-fav absolute right-3 top-3 text-white/80 transition hover:scale-110">
            <svg class="size-6 drop-shadow" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
              <path d="M12 21s-7.5-4.6-9.6-9.2C.9 8.4 2.7 5 6 5c2 0 3.3 1.1 4 2.2h4C14.7 6.1 16 5 18 5c3.3 0 5.1 3.4 3.6 6.8C19.5 16.4 12 21 12 21z"/>
            </svg>
          </button>
        </div>
        <div class="p-3">
          <p class="text-sm font-semibold text-brand-green-3">${escapeHtml(depot.nama)}</p>
          <p class="mt-1 text-xs text-slate-600">${escapeHtml(depot.kelurahan)}</p>
          <p class="mt-2 text-xs text-slate-600">${escapeHtml(depot.alamat)}</p>
          <dl class="mt-3 space-y-1 text-xs text-slate-600">
            <div class="flex justify-between gap-3"><dt>Harga per galon</dt><dd class="font-medium">${escapeHtml(formatPrice(depot.harga))}</dd></div>
            <div class="flex justify-between gap-3"><dt>Jam operasional</dt><dd>${escapeHtml(formatTime(depot.jam_buka))} - ${escapeHtml(formatTime(depot.jam_tutup))}</dd></div>
            <div class="flex justify-between gap-3"><dt>Layanan antar</dt><dd>${depot.antar ? 'Ya' : 'Tidak'}</dd></div>
            <div class="flex justify-between gap-3"><dt>Buka setiap hari</dt><dd>${depot.buka_setiap_hari ? 'Ya' : 'Tidak'}</dd></div>
          </dl>
          <p class="mt-2 text-xs text-slate-600">${hariLibur.length ? `Hari libur: ${escapeHtml(hariLibur.join(', '))}` : 'Hari libur belum dicatat'}</p>
          ${depot.telepon ? `<p class="mt-2 text-xs text-slate-600">Telepon: ${escapeHtml(depot.telepon)}</p>` : ''}
          ${mapAction}
        </div>
      </div>`;
};

// ---------- Elemen filter ----------
const $ = (id) => document.getElementById(id);
const fQ = $('f-q'), fKel = $('f-kel'), fHarga = $('f-harga');
const fAntar = $('f-antar'), fBuka = $('f-buka');
const listEl = $('hasil'), jumlahEl = $('hasil-jumlah'), hargaLabel = $('harga-label');

const rupiah = (number) => `Rp${Number(number).toLocaleString('id-ID')}`;

// ---------- Render ----------
function render() {
    const q = fQ.value.trim().toLowerCase();
    const kel = fKel.value;
    const maxHarga = Number(fHarga.value);

    const hasil = depots.filter((depot) => {
        const searchableText = `${depot.nama} ${depot.kelurahan} ${depot.alamat}`.toLowerCase();

        return (!q || searchableText.includes(q)) &&
            (!kel || depot.kelurahan === kel) &&
            depot.harga <= maxHarga &&
            (!fAntar.checked || depot.antar) &&
            (!fBuka.checked || depot.buka_setiap_hari);
    });

    layer.clearLayers();
    markers.clear();
    listEl.innerHTML = '';

    hasil.forEach((depot) => {
        const marker = L.marker([depot.lat, depot.lng], { icon: pinIcon })
            .bindPopup(popupHtml(depot), { closeButton: false, minWidth: 260, maxWidth: 280 })
            .addTo(layer);
        markers.set(String(depot.id), marker);

        const item = document.createElement('li');
        item.innerHTML = `
          <button type="button" class="flex w-full items-stretch overflow-hidden rounded-xl bg-white text-left shadow-md shadow-slate-300/60 transition hover:shadow-lg">
            <img src="${escapeHtml(depotPlaceholder)}" alt="" class="h-20 w-20 shrink-0 object-cover" loading="lazy">
            <span class="flex min-w-0 flex-col justify-center px-3 py-3">
              <span class="truncate text-sm font-medium text-brand-green-3">${escapeHtml(depot.nama)}</span>
              <span class="mt-1 truncate text-[11px] text-slate-600">${escapeHtml(depot.kelurahan)} - ${escapeHtml(depot.alamat)}</span>
              <span class="mt-1 text-[11px] text-brand-green-3">${escapeHtml(formatPrice(depot.harga))} per galon</span>
              <span class="text-[11px] text-slate-600">Jam ${escapeHtml(formatTime(depot.jam_buka))} - ${escapeHtml(formatTime(depot.jam_tutup))}</span>
            </span>
          </button>`;

        item.firstElementChild.addEventListener('click', () => {
            map.flyTo([depot.lat, depot.lng], 16, { duration: 0.8 });
            marker.openPopup();
        });
        listEl.appendChild(item);
    });

    jumlahEl.textContent = `(${hasil.length})`;
    if (!hasil.length) {
        listEl.innerHTML = '<li class="text-xs text-slate-500">Tidak ada depot yang cocok.</li>';
    } else {
        map.fitBounds(L.latLngBounds(hasil.map((depot) => [depot.lat, depot.lng])), { padding: [80, 80], maxZoom: 16 });
    }
}

// ---------- Event ----------
$('f-apply').addEventListener('click', render);
fQ.addEventListener('input', render);
fHarga.addEventListener('input', () => { hargaLabel.textContent = rupiah(Number(fHarga.value)); });

// Panel di HP
$('panel-toggle').addEventListener('click', () => $('panel').classList.toggle('hidden'));

// Kata kunci dari search bar di hero (?q=...)
const query = new URLSearchParams(location.search).get('q');
if (query) fQ.value = query;

render();

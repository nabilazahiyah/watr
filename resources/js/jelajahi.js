import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import { syncFavoriteButtons } from './favorites';

const depots = Array.isArray(window.DEPOTS) ? window.DEPOTS : [];
const depotPlaceholder = window.DEPOT_PLACEHOLDER || '/images/fotodummy.jpg';
const $ = (id) => document.getElementById(id);
const PUSAT = [-6.5985, 106.7975];

const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (character) => ({
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#039;',
}[character]));

const rupiah = (value) => `Rp${Number(value).toLocaleString('id-ID')}`;
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

// ---------- Peta dan peta dasar ----------
const map = L.map('map', { zoomControl: false }).setView(PUSAT, 14);
const basemaps = {
    jalan: L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors',
    }),
    satelit: L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
        maxZoom: 19,
        attribution: 'Tiles &copy; Esri',
    }),
};
let aktifLayer = basemaps.jalan.addTo(map);

['panel', 'layer-panel', 'legend-panel', 'layer-btn'].forEach((id) => {
    const element = $(id);
    if (element) {
        L.DomEvent.disableClickPropagation(element);
        L.DomEvent.disableScrollPropagation(element);
    }
});

$('zoom-in').addEventListener('click', () => map.zoomIn());
$('zoom-out').addEventListener('click', () => map.zoomOut());

let lokasiMarker;
$('locate-btn').addEventListener('click', () => map.locate({ setView: true, maxZoom: 16 }));
map.on('locationfound', (event) => {
    lokasiMarker?.remove();
    lokasiMarker = L.circleMarker(event.latlng, {
        radius: 8,
        color: '#fff',
        weight: 3,
        fillColor: '#0ACBFE',
        fillOpacity: 1,
    }).addTo(map);
});
map.on('locationerror', () => alert('Lokasi tidak dapat diakses. Pastikan izin lokasi di browser sudah diaktifkan.'));

document.querySelectorAll('.base-opt').forEach((button) => {
    button.addEventListener('click', () => {
        map.removeLayer(aktifLayer);
        aktifLayer = basemaps[button.dataset.base].addTo(map);
        aktifLayer.bringToBack();

        document.querySelectorAll('.base-opt').forEach((option) => {
            const active = option === button;
            option.classList.toggle('bg-sky-100', active);
            option.classList.toggle('text-brand-blue', active);
            option.classList.toggle('text-slate-600', !active);
        });
    });
});

// ---------- Marker dan layer depot ----------
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

const depotLayer = L.layerGroup().addTo(map);
const markers = new Map();

// Layer berikut hanya geometri/titik contoh untuk pratinjau UI; bukan data resmi
// dan tidak digabungkan dengan marker depot dari PostgreSQL.
const healthIcon = L.divIcon({
    className: 'pin-depot',
    html: `<span style="display:flex;width:28px;height:28px;align-items:center;justify-content:center;
                border-radius:9999px;background:#0C9624;color:#fff;font:700 18px/1 sans-serif;
                border:2px solid #fff;box-shadow:0 2px 6px rgb(0 0 0/.3)">+</span>`,
    iconSize: [28, 28],
    iconAnchor: [14, 14],
});

const kesehatanLayer = L.layerGroup([
    L.marker([-6.5935, 106.8010], { icon: healthIcon }).bindTooltip('Fasilitas kesehatan (titik contoh)'),
    L.marker([-6.6050, 106.7960], { icon: healthIcon }).bindTooltip('Fasilitas kesehatan (titik contoh)'),
]);
const sungaiLayer = L.layerGroup([
    L.polyline([[-6.575, 106.800], [-6.590, 106.803], [-6.605, 106.806], [-6.620, 106.808]], { color: '#0ACBFE', weight: 4 }),
]);
const wilayahLayer = L.layerGroup([
    L.polygon([[-6.580, 106.780], [-6.580, 106.810], [-6.615, 106.810], [-6.615, 106.780]], {
        color: '#94a3b8',
        weight: 1,
        fillColor: '#cbd5e1',
        fillOpacity: 0.35,
    }),
]);
const kelurahanLayer = L.layerGroup([
    L.polyline([[-6.580, 106.795], [-6.615, 106.795]], { color: '#64748b', weight: 2, dashArray: '6 6' }),
    L.polyline([[-6.597, 106.780], [-6.597, 106.810]], { color: '#64748b', weight: 2, dashArray: '6 6' }),
]);
const overlays = {
    depot: depotLayer,
    kesehatan: kesehatanLayer,
    sungai: sungaiLayer,
    wilayah: wilayahLayer,
    kelurahan: kelurahanLayer,
};

document.querySelectorAll('.ov-check').forEach((checkbox) => {
    checkbox.addEventListener('change', () => {
        const overlay = overlays[checkbox.dataset.ov];
        if (!overlay) return;

        if (checkbox.checked) overlay.addTo(map);
        else map.removeLayer(overlay);
    });
});

const layerPanel = $('layer-panel');
const legendPanel = $('legend-panel');
$('layer-btn').addEventListener('click', () => {
    const hidden = layerPanel.classList.toggle('hidden');
    if (hidden) legendPanel.classList.add('hidden');
});
$('layer-close').addEventListener('click', () => {
    layerPanel.classList.add('hidden');
    legendPanel.classList.add('hidden');
});
$('legend-open').addEventListener('click', () => legendPanel.classList.toggle('hidden'));
$('legend-close').addEventListener('click', () => legendPanel.classList.add('hidden'));

// ---------- Popup depot ----------
const popupHtml = (depot) => {
    const mapUrl = safeMapsUrl(depot.link_google_maps);
    const holidays = Array.isArray(depot.hari_libur) ? depot.hari_libur : [];
    const mapAction = mapUrl
        ? `<a href="${escapeHtml(mapUrl)}" target="_blank" rel="noopener noreferrer" class="mt-3 block rounded-lg border border-slate-300 py-2 text-center text-xs font-medium text-slate-700 transition hover:bg-slate-50">Buka Google Maps</a>`
        : '<p class="mt-3 text-xs text-slate-500">Link peta belum tersedia.</p>';

    return `
      <div class="p-2">
        <div class="relative">
          <img src="${escapeHtml(depotPlaceholder)}" alt="Foto simulasi ${escapeHtml(depot.nama)}" class="aspect-4/3 w-full rounded-xl object-cover">
          <button type="button" class="popup-close absolute right-2 top-2 flex size-8 items-center justify-center rounded-full bg-white/90 text-slate-600 shadow transition hover:bg-white" aria-label="Tutup">
            <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/></svg>
          </button>
          <button type="button" data-favorite-id="${escapeHtml(depot.id)}" aria-label="Tambahkan ke favorit" aria-pressed="false"
                  class="popup-fav absolute right-12 top-2 flex size-8 items-center justify-center text-white/80 transition hover:scale-105">
            <svg class="size-5 drop-shadow" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
              <path d="M12 21s-7.5-4.6-9.6-9.2C.9 8.4 2.7 5 6 5c2 0 3.3 1.1 4 2.2h4C14.7 6.1 16 5 18 5c3.3 0 5.1 3.4 3.6 6.8C19.5 16.4 12 21 12 21z"/>
            </svg>
          </button>
        </div>
        <div class="px-1 pb-1 pt-3">
          <p class="text-base font-semibold text-brand-green-3">${escapeHtml(depot.nama)}</p>
          <p class="mt-1 text-xs text-slate-500">${escapeHtml(depot.kelurahan)}</p>
          <p class="mt-2 text-xs text-slate-600">${escapeHtml(depot.alamat)}</p>
          <dl class="mt-3 space-y-1 text-xs text-slate-600">
            <div class="flex justify-between gap-3"><dt>Harga per galon</dt><dd class="font-medium">${escapeHtml(rupiah(depot.harga))}</dd></div>
            <div class="flex justify-between gap-3"><dt>Jam operasional</dt><dd>${escapeHtml(formatTime(depot.jam_buka))}–${escapeHtml(formatTime(depot.jam_tutup))}</dd></div>
            <div class="flex justify-between gap-3"><dt>Layanan antar</dt><dd>${depot.antar ? 'Ya' : 'Tidak'}</dd></div>
            <div class="flex justify-between gap-3"><dt>Buka setiap hari</dt><dd>${depot.buka_setiap_hari ? 'Ya' : 'Tidak'}</dd></div>
          </dl>
          <p class="mt-2 text-xs text-slate-600">${holidays.length ? `Hari libur: ${escapeHtml(holidays.join(', '))}` : 'Hari libur belum dicatat'}</p>
          ${depot.telepon ? `<p class="mt-2 text-xs text-slate-600">Telepon: ${escapeHtml(depot.telepon)}</p>` : ''}
          ${mapAction}
        </div>
      </div>`;
};

// ---------- Filter dan daftar hasil ----------
const fQ = $('f-q');
const fKel = $('f-kel');
const fHarga = $('f-harga');
const fAntar = $('f-antar');
const fBuka = $('f-buka');
const listEl = $('hasil');
const jumlahEl = $('hasil-jumlah');
const hargaLabel = $('harga-label');
const wrapEl = $('hasil-wrap');
let listAktif = false;

function render() {
    const query = fQ.value.trim().toLocaleLowerCase('id-ID');
    const kelurahan = fKel.value;
    const maxHarga = Number(fHarga.value);
    const results = depots.filter((depot) => {
        const searchable = `${depot.nama} ${depot.kelurahan} ${depot.alamat}`.toLocaleLowerCase('id-ID');

        return (!query || searchable.includes(query)) &&
            (!kelurahan || depot.kelurahan === kelurahan) &&
            Number(depot.harga) <= maxHarga &&
            (!fAntar.checked || depot.antar) &&
            (!fBuka.checked || depot.buka_setiap_hari);
    });

    depotLayer.clearLayers();
    markers.clear();
    listEl.innerHTML = '';

    results.forEach((depot) => {
        const marker = L.marker([depot.lat, depot.lng], { icon: pinIcon })
            .bindPopup(popupHtml(depot), { closeButton: false, minWidth: 260, maxWidth: 280 })
            .addTo(depotLayer);
        markers.set(String(depot.id), marker);

        const item = document.createElement('li');
        item.innerHTML = `
          <button type="button" data-depot-detail-id="${escapeHtml(depot.id)}"
                  class="flex w-full items-stretch overflow-hidden rounded-xl bg-white text-left shadow-md shadow-slate-300/60 transition hover:shadow-lg"
                  aria-label="Lihat detail depot ${escapeHtml(depot.nama)}">
            <img src="${escapeHtml(depotPlaceholder)}" alt="" class="w-20 shrink-0 object-cover" loading="lazy">
            <span class="flex min-w-0 flex-col justify-center px-3 py-3">
              <span class="truncate text-sm font-medium text-brand-green-3">${escapeHtml(depot.nama)}</span>
              <span class="mt-1 truncate text-[11px] text-slate-600">${escapeHtml(depot.kelurahan)} · ${escapeHtml(depot.alamat)}</span>
              <span class="mt-1 text-[11px] text-brand-green-3">${escapeHtml(rupiah(depot.harga))} per galon</span>
              <span class="text-[11px] text-slate-600">Lihat detail depot</span>
            </span>
          </button>`;

        item.querySelector('[data-depot-detail-id]').addEventListener('click', () => {
            map.flyTo([depot.lat, depot.lng], 16, { duration: 0.8 });
            marker.openPopup();
        });
        listEl.appendChild(item);
    });

    jumlahEl.textContent = `(${results.length})`;
    if (!results.length) {
        listEl.innerHTML = `<li class="text-xs text-slate-500">${depots.length ? 'Tidak ada depot yang cocok.' : 'Belum ada data depot.'}</li>`;
    } else if (listAktif) {
        map.fitBounds(L.latLngBounds(results.map((depot) => [depot.lat, depot.lng])), { padding: [80, 80], maxZoom: 16 });
    }

    wrapEl.classList.toggle('hidden', !listAktif);
}

$('f-apply').addEventListener('click', () => {
    listAktif = true;
    render();
});
fQ.addEventListener('input', () => {
    listAktif = fQ.value.trim() !== '';
    render();
});
fHarga.addEventListener('input', () => {
    hargaLabel.textContent = rupiah(Number(fHarga.value));
});
$('f-reset').addEventListener('click', () => {
    fQ.value = '';
    fKel.value = '';
    fHarga.value = fHarga.max;
    hargaLabel.textContent = rupiah(Number(fHarga.max));
    fAntar.checked = false;
    fBuka.checked = false;
    history.replaceState(null, '', location.pathname);
    listAktif = false;
    render();
    map.flyTo(PUSAT, 14, { duration: 0.8 });
});

map.on('popupopen', (event) => {
    const popupElement = event.popup.getElement();
    syncFavoriteButtons(popupElement);
    popupElement.querySelector('.popup-close')?.addEventListener('click', () => map.closePopup());
});

$('panel-toggle').addEventListener('click', () => $('panel').classList.toggle('hidden'));

const initialQuery = new URLSearchParams(location.search).get('q');
if (initialQuery) {
    fQ.value = initialQuery;
    listAktif = true;
}

render();

import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

const depots = window.DEPOTS ?? [];
const FOTO = '/images/depot/';
const $ = (id) => document.getElementById(id);
const PUSAT = [-6.5985, 106.7975];

// ---------- Helper ----------
const rupiah = (n) => 'Rp' + n.toLocaleString('id-ID');
const koma = (n) => String(n).replace('.', ',');
const bintang = (rating) => {
    const persen = Math.max(0, Math.min(5, rating)) / 5 * 100;
    return `
      <span class="relative inline-block leading-none" aria-label="Rating ${rating} dari 5">
        <span class="text-slate-300">★★★★★</span>
        <span class="absolute inset-y-0 left-0 overflow-hidden whitespace-nowrap text-yellow-400" style="width:${persen}%">★★★★★</span>
      </span>`;
};

// ---------- Peta ----------
const map = L.map('map', { zoomControl: false }).setView(PUSAT, 14);

const basemaps = {
    jalan: L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19, attribution: '&copy; OpenStreetMap contributors',
    }),
    satelit: L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
        maxZoom: 19, attribution: 'Tiles &copy; Esri',
    }),
};
let aktifLayer = basemaps.jalan.addTo(map);

// Klik/scroll di atas panel tidak ikut menggeser peta
['panel', 'layer-panel', 'legend-panel', 'layer-btn'].forEach((id) => {
    const el = $(id);
    if (el) {
        L.DomEvent.disableClickPropagation(el);
        L.DomEvent.disableScrollPropagation(el);
    }
});

// Zoom
$('zoom-in').addEventListener('click', () => map.zoomIn());
$('zoom-out').addEventListener('click', () => map.zoomOut());

// Lokasi saya
let lokasiMarker;
$('locate-btn').addEventListener('click', () => map.locate({ setView: true, maxZoom: 16 }));
map.on('locationfound', (e) => {
    lokasiMarker?.remove();
    lokasiMarker = L.circleMarker(e.latlng, {
        radius: 8, color: '#fff', weight: 3, fillColor: '#0ACBFE', fillOpacity: 1,
    }).addTo(map);
});
map.on('locationerror', () => alert('Lokasi tidak dapat diakses. Pastikan izin lokasi di browser sudah diaktifkan.'));

// ---------- Pin & layer ----------
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

// Layer tambahan (data contoh, ganti dengan GeoJSON asli)
const healthIcon = L.divIcon({
    className: 'pin-depot',
    html: `<span style="display:flex;width:28px;height:28px;align-items:center;justify-content:center;
                border-radius:9999px;background:#0C9624;color:#fff;font:700 18px/1 sans-serif;
                border:2px solid #fff;box-shadow:0 2px 6px rgb(0 0 0/.3)">+</span>`,
    iconSize: [28, 28],
    iconAnchor: [14, 14],
});

const kesehatanLayer = L.layerGroup([
    L.marker([-6.5935, 106.8010], { icon: healthIcon }).bindTooltip('Puskesmas (contoh)'),
    L.marker([-6.6050, 106.7960], { icon: healthIcon }).bindTooltip('Klinik (contoh)'),
]).addTo(map);

const sungaiLayer = L.layerGroup([
    L.polyline([[-6.575, 106.800], [-6.590, 106.803], [-6.605, 106.806], [-6.620, 106.808]],
        { color: '#0ACBFE', weight: 4 }),
]);

const wilayahLayer = L.layerGroup([
    L.polygon([[-6.580, 106.780], [-6.580, 106.810], [-6.615, 106.810], [-6.615, 106.780]],
        { color: '#94a3b8', weight: 1, fillColor: '#cbd5e1', fillOpacity: 0.35 }),
]);

const kelurahanLayer = L.layerGroup([
    L.polyline([[-6.580, 106.795], [-6.615, 106.795]], { color: '#64748b', weight: 2, dashArray: '6 6' }),
    L.polyline([[-6.597, 106.780], [-6.597, 106.810]], { color: '#64748b', weight: 2, dashArray: '6 6' }),
]);

const overlays = {
    depot: layer,
    kesehatan: kesehatanLayer,
    sungai: sungaiLayer,
    wilayah: wilayahLayer,
    kelurahan: kelurahanLayer,
};

// Centang / hapus centang layer
document.querySelectorAll('.ov-check').forEach((cb) => {
    cb.addEventListener('change', () => {
        const l = overlays[cb.dataset.ov];
        if (cb.checked) l.addTo(map);
        else map.removeLayer(l);
    });
});

// Peta dasar: Jalan / Satelit
document.querySelectorAll('.base-opt').forEach((btn) => {
    btn.addEventListener('click', () => {
        map.removeLayer(aktifLayer);
        aktifLayer = basemaps[btn.dataset.base].addTo(map);
        aktifLayer.bringToBack();

        document.querySelectorAll('.base-opt').forEach((b) => {
            const on = b === btn;
            b.classList.toggle('bg-sky-100', on);
            b.classList.toggle('text-brand-blue', on);
            b.classList.toggle('text-slate-600', !on);
        });
    });
});

// Buka / tutup panel Layer peta dan Legenda
const layerPanel = $('layer-panel');
const legendPanel = $('legend-panel');

$('layer-btn').addEventListener('click', () => {
    const tersembunyi = layerPanel.classList.toggle('hidden');
    if (tersembunyi) legendPanel.classList.add('hidden');
});
$('layer-close').addEventListener('click', () => {
    layerPanel.classList.add('hidden');
    legendPanel.classList.add('hidden');
});
$('legend-open').addEventListener('click', () => legendPanel.classList.toggle('hidden'));
$('legend-close').addEventListener('click', () => legendPanel.classList.add('hidden'));

// ---------- Kartu popup ----------
const popupHtml = (d) => {
    const info = [d.buka24 && 'Buka 24 jam', d.antar && 'Layanan antar'].filter(Boolean).join(' · ');

    return `
      <div class="p-2">
        <div class="relative">
          <img src="${FOTO + d.foto}" alt="${d.nama}" class="aspect-4/3 w-full rounded-xl object-cover">
          <button type="button" class="popup-close absolute right-2 top-2 flex size-8 items-center justify-center rounded-full bg-white/90 text-slate-600 shadow transition hover:bg-white" aria-label="Tutup">
            <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/>
            </svg>
          </button>
        </div>

        <div class="px-1 pb-1 pt-3">
          <p class="text-base font-semibold text-brand-green-3">${d.nama}</p>
          <p class="mt-1 text-xs text-slate-500">${koma(d.rating)} · ${d.ulasan} Google Reviews</p>
          ${info ? `<p class="mt-2 text-xs text-brand-green-2">${info}</p>` : ''}
          <p class="mt-3 text-sm font-semibold text-brand-green-3">
            ${rupiah(d.harga_min)} – ${rupiah(d.harga_max)} / galon
          </p>
          <a href="#" class="btn-popup mt-4 block rounded-full bg-brand-blue py-2.5 text-center text-sm font-medium text-white transition hover:brightness-95">
  Lihat detail depot
</a>
        </div>
      </div>`;
};
// ---------- Elemen filter ----------
const fQ = $('f-q'), fKel = $('f-kel'), fHarga = $('f-harga');
const fAntar = $('f-antar'), f24 = $('f-24');
const listEl = $('hasil'), jumlahEl = $('hasil-jumlah'), hargaLabel = $('harga-label');
const wrapEl = $('hasil-wrap');
let listAktif = false; // daftar hasil tampil hanya setelah filter dipakai

// ---------- Render ----------
function render() {
    const q = fQ.value.trim().toLowerCase();
    const kel = fKel.value;
    const maxHarga = Number(fHarga.value);

    const hasil = depots.filter((d) =>
    (!q || d.nama.toLowerCase().includes(q) || d.kelurahan.toLowerCase().includes(q)) &&
    (!kel || d.kelurahan === kel) &&
    d.harga_min <= maxHarga &&
    (!fAntar.checked || d.antar) &&
    (!f24.checked || d.buka24)
);
    layer.clearLayers();
    markers.clear();
    listEl.innerHTML = '';

    hasil.forEach((d) => {
        const m = L.marker([d.lat, d.lng], { icon: pinIcon })
            .bindPopup(popupHtml(d), { closeButton: false, minWidth: 260, maxWidth: 260 })
            .addTo(layer);
        markers.set(d.id, m);

        const li = document.createElement('li');
        li.innerHTML = `
          <button type="button"
                  class="flex w-full items-stretch overflow-hidden rounded-xl bg-white text-left shadow-md shadow-slate-300/60 transition hover:shadow-lg">
            <img src="${FOTO + d.foto}" alt="${d.nama}" class="w-20 shrink-0 object-cover">
            <span class="flex min-w-0 flex-col justify-center px-3 py-3">
              <span class="truncate text-sm font-medium text-brand-green-3">${d.nama}</span>
              <span class="mt-1 flex flex-wrap items-center gap-x-1.5 text-[11px] text-brand-green-3">
                <span>${koma(d.rating)}</span>
                ${bintang(d.rating)}
                <span>· ${d.ulasan} Google Reviews</span>
              </span>
            </span>
          </button>`;
        li.firstElementChild.addEventListener('click', () => {
            map.flyTo([d.lat, d.lng], 16, { duration: 0.8 });
            m.openPopup();
        });
        listEl.appendChild(li);
    });

    jumlahEl.textContent = `(${hasil.length})`;

    if (!hasil.length) {
        listEl.innerHTML = '<li class="text-xs text-slate-500">Tidak ada depot yang cocok.</li>';
    } else if (listAktif) {
        // Zoom ke hasil hanya saat filter dipakai
        map.fitBounds(L.latLngBounds(hasil.map((d) => [d.lat, d.lng])), { padding: [80, 80], maxZoom: 16 });
    }

    wrapEl.classList.toggle('hidden', !listAktif);
}

// ---------- Event filter ----------
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

// Reset semua filter ke kondisi awal
$('f-reset').addEventListener('click', () => {
    fQ.value = '';
    fKel.value = '';
    fHarga.value = fHarga.max;
    hargaLabel.textContent = rupiah(Number(fHarga.max));
    fAntar.checked = false;
    f24.checked = false;

    // Hapus ?q=... dari URL supaya tidak terbawa lagi
    history.replaceState(null, '', location.pathname);

    listAktif = false;
    render();
    map.flyTo(PUSAT, 14, { duration: 0.8 });
});

// Tombol close di popup
map.on('popupopen', (e) => {
    e.popup.getElement().querySelector('.popup-close')
        ?.addEventListener('click', () => map.closePopup());
});

// Panel di HP
$('panel-toggle').addEventListener('click', () => $('panel').classList.toggle('hidden'));

// Kata kunci dari search bar di hero (?q=...)
const qs = new URLSearchParams(location.search).get('q');
if (qs) {
    fQ.value = qs;
    listAktif = true;
}

render();
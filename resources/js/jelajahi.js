import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

const depots = window.DEPOTS ?? [];
const FOTO = '/images/depot/';

// ---------- Peta ----------
const map = L.map('map', { zoomControl: false }).setView([-6.5985, 106.7975], 14);
L.control.zoom({ position: 'bottomright' }).addTo(map);

L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '&copy; OpenStreetMap contributors',
}).addTo(map);

// Pin biru
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

// ---------- Kartu popup ----------
const popupHtml = (d) => `
  <div>
    <div class="relative p-2 pb-0">
      <img src="${FOTO + d.foto}" alt="${d.nama}" class="aspect-4/3 w-full rounded-lg object-cover">
      <button type="button" class="popup-fav absolute right-4 top-4 text-white/80 transition hover:scale-110" aria-label="Favorit">
        <svg class="size-6 drop-shadow" viewBox="0 0 24 24" fill="currentColor">
          <path d="M12 21s-7.5-4.6-9.6-9.2C.9 8.4 2.7 5 6 5c2 0 3.3 1.1 4 2.2h4C14.7 6.1 16 5 18 5c3.3 0 5.1 3.4 3.6 6.8C19.5 16.4 12 21 12 21z"/>
        </svg>
      </button>
    </div>
    <div class="p-3">
      <p class="text-sm font-semibold text-brand-green-3">${d.nama}</p>
      <p class="mt-1 text-xs text-slate-600">
        ${String(d.rating).replace('.', ',')}
        ${bintang(d.rating)} · ${d.ulasan} Google Reviews
      </p>
      <a href="#" class="mt-3 block rounded-lg border border-slate-300 py-2 text-center text-xs font-medium text-slate-700 transition hover:bg-slate-50">
        Lihat Detail Informasi
      </a>
    </div>
  </div>`;

// ---------- Elemen filter ----------
const $ = (id) => document.getElementById(id);
const fQ = $('f-q'), fKel = $('f-kel'), fHarga = $('f-harga');
const fAntar = $('f-antar'), f24 = $('f-24');
const listEl = $('hasil'), jumlahEl = $('hasil-jumlah'), hargaLabel = $('harga-label');

const rupiah = (n) => 'Rp' + n.toLocaleString('id-ID');
const bintang = (rating) => {
    const persen = Math.max(0, Math.min(5, rating)) / 5 * 100;
    return `
      <span class="relative inline-block leading-none" aria-label="Rating ${rating} dari 5">
        <span class="text-slate-300">★★★★★</span>
        <span class="absolute inset-y-0 left-0 overflow-hidden whitespace-nowrap text-yellow-400" style="width:${persen}%">★★★★★</span>
      </span>`;
};

const koma = (n) => String(n).replace('.', ',');

// ---------- Render ----------
function render() {
    const q = fQ.value.trim().toLowerCase();
    const kel = fKel.value;
    const maxHarga = Number(fHarga.value);

    const hasil = depots.filter((d) =>
        (!q || d.nama.toLowerCase().includes(q) || d.kelurahan.toLowerCase().includes(q)) &&
        (!kel || d.kelurahan === kel) &&
        d.harga <= maxHarga &&
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
    } else {
        map.fitBounds(L.latLngBounds(hasil.map((d) => [d.lat, d.lng])), { padding: [80, 80], maxZoom: 16 });
    }
}

// ---------- Event ----------
$('f-apply').addEventListener('click', render);
fQ.addEventListener('input', render);
fHarga.addEventListener('input', () => { hargaLabel.textContent = rupiah(Number(fHarga.value)); });

// Tombol favorit di popup
map.on('popupopen', (e) => {
    const btn = e.popup.getElement().querySelector('.popup-fav');
    btn?.addEventListener('click', () => {
        btn.classList.toggle('text-red-500');
        btn.classList.toggle('text-white/80');
    });
});

// Panel di HP
$('panel-toggle').addEventListener('click', () => $('panel').classList.toggle('hidden'));

// Kata kunci dari search bar di hero (?q=...)
const qs = new URLSearchParams(location.search).get('q');
if (qs) fQ.value = qs;

render();
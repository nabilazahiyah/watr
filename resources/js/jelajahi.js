import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import { getFavoriteIds, syncFavoriteButtons, toggleFavorite } from './favorites';

const depots = window.DEPOTS ?? [];
const $ = (id) => document.getElementById(id);
const PUSAT = [-6.5985, 106.7975];

// ---------- Helper ----------
const rupiah = (n) => 'Rp' + n.toLocaleString('id-ID');
const hargaText = (d) => d.harga_min
    ? `${rupiah(d.harga_min)} – ${rupiah(d.harga_max)} per galon`
    : 'Harga belum tersedia';
const jamText = (d) => `Jam buka ${String(d.jam_buka ?? '').slice(0, 5)}–${String(d.jam_tutup ?? '').slice(0, 5)}`;
const jadwalText = (d) => d.buka_setiap_hari ? 'Buka setiap hari' : 'Tidak buka setiap hari';
const hariLiburText = (d) => d.hari_libur?.length ? `Hari libur: ${d.hari_libur.join(', ')}` : 'Jadwal hari libur belum tersedia';
// ---------- Peta ----------
const map = L.map('map', { zoomControl: false }).setView(PUSAT, 14);
const MAP_KECAMATAN_PANE = 'kecamatanBoundaryPane';
const MAP_KELURAHAN_PANE = 'kelurahanBoundaryPane';
const MAP_SUNGAI_PANE = 'sungaiPane';
const MAP_FASKES_PANE = 'faskesPane';
map.createPane(MAP_KECAMATAN_PANE);
map.createPane(MAP_KELURAHAN_PANE);
map.createPane(MAP_SUNGAI_PANE);
map.createPane(MAP_FASKES_PANE);
map.getPane(MAP_KECAMATAN_PANE).style.zIndex = '430';
map.getPane(MAP_KELURAHAN_PANE).style.zIndex = '450';
map.getPane(MAP_SUNGAI_PANE).style.zIndex = '410';
map.getPane(MAP_FASKES_PANE).style.zIndex = '580';

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
['panel', 'layer-panel', 'legend-panel', 'layer-btn', 'fav-panel', 'fav-btn'].forEach((id) => {
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
map.on('locationerror', () => alert('Lokasi tidak dapat ditemukan. Periksa izin lokasi di browser, lalu coba lagi.'));

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

const healthIcon = L.divIcon({
    className: 'pin-depot',
    html: `<span style="display:flex;width:28px;height:28px;align-items:center;justify-content:center;
                border-radius:9999px;background:#0C9624;color:#fff;font:700 18px/1 sans-serif;
                border:2px solid #fff;box-shadow:0 2px 6px rgb(0 0 0/.3)">+</span>`,
    iconSize: [28, 28],
    iconAnchor: [14, 14],
});

let skippedFaskesFeatures = 0;

function faskesPopup(properties = {}) {
    const popup = document.createElement('div');
    popup.className = 'space-y-2 text-sm';

    const title = document.createElement('p');
    title.className = 'font-semibold text-brand-green-3';
    title.textContent = properties.nama_faskes || 'Fasilitas kesehatan';
    popup.appendChild(title);

    const category = String(properties.jenis_faskes ?? '').trim();
    if (category) {
        const label = document.createElement('span');
        label.className = 'inline-block rounded-full bg-sky-100 px-2.5 py-1 text-xs font-medium text-sky-800';
        label.textContent = category;
        popup.appendChild(label);
    }

    const kelurahan = String(properties.kelurahan ?? '').trim();
    const address = String(properties.alamat ?? '').trim();
    const addressParts = address.split(',').map((part) => part.trim()).filter(Boolean);
    const conciseAddress = addressParts
        .filter((part) => !kelurahan || part.localeCompare(kelurahan, 'id', { sensitivity: 'base' }) !== 0)
        .join(', ');

    [
        ['Alamat', conciseAddress],
        ['Kelurahan', kelurahan],
    ].forEach(([label, value]) => {
        if (value === null || value === undefined || String(value).trim() === '') return;

        const detail = document.createElement('p');
        detail.className = 'text-xs text-slate-600';
        detail.textContent = `${label}: ${value}`;
        popup.appendChild(detail);
    });

    return popup;
}

const kesehatanLayer = L.geoJSON(null, {
    pane: MAP_FASKES_PANE,
    filter: (feature) => {
        const coordinates = feature?.geometry?.coordinates;
        const valid = feature?.geometry?.type === 'Point'
            && Array.isArray(coordinates)
            && coordinates.length >= 2
            && Number.isFinite(coordinates[0])
            && Number.isFinite(coordinates[1])
            && coordinates[0] >= -180 && coordinates[0] <= 180
            && coordinates[1] >= -90 && coordinates[1] <= 90;

        if (!valid) skippedFaskesFeatures++;
        return valid;
    },
    pointToLayer: (feature, latlng) => {
        return L.marker(latlng, { icon: healthIcon });
    },
    onEachFeature: (feature, featureLayer) => {
        featureLayer.bindPopup(faskesPopup(feature.properties ?? {}));
    },
});

const sungaiStyle = {
    color: '#0284c7',
    weight: 3,
    opacity: 0.9,
    fill: false,
};

function riverPopup(properties = {}) {
    const popup = document.createElement('div');
    popup.className = 'space-y-1 text-sm';

    const nama = String(properties.nama_sungai ?? '').trim();
    const title = document.createElement('p');
    title.className = 'font-semibold text-brand-green-3';
    title.textContent = nama || 'Jaringan sungai';
    popup.appendChild(title);

    return popup;
}

const sungaiLayer = L.geoJSON(null, {
    pane: MAP_SUNGAI_PANE,
    style: () => sungaiStyle,
    onEachFeature: (feature, featureLayer) => {
        featureLayer.bindPopup(riverPopup(feature.properties ?? {}));
        featureLayer.on('mouseover', (event) => {
            event.target.setStyle({ color: '#0369a1', weight: 4, opacity: 1 });
        });
        featureLayer.on('mouseout', (event) => event.target.setStyle(sungaiStyle));
    },
});

const kecamatanNormalStyle = {
    color: '#dc2626',
    weight: 3,
    opacity: 1,
    fill: true,
    fillColor: '#dc2626',
    fillOpacity: 0.001,
};
const kecamatanSelectedStyle = {
    color: '#2563eb',
    weight: 4,
    opacity: 1,
    fill: true,
    fillColor: '#3b82f6',
    fillOpacity: 0.2,
};
const kelurahanNormalStyle = {
    color: '#64748b',
    weight: 1,
    opacity: 0.85,
    fill: true,
    fillColor: '#94a3b8',
    fillOpacity: 0.035,
};
const kelurahanSelectedStyle = {
    color: '#2563eb',
    weight: 3,
    opacity: 1,
    fill: true,
    fillColor: '#3b82f6',
    fillOpacity: 0.22,
};

let selectedKelurahan = '';
let kecamatanSelected = false;
const kelurahanFeatureLayers = new Map();

function selectKelurahan(nama) {
    selectedKelurahan = nama || '';
    $('f-kel').value = selectedKelurahan;

    kelurahanFeatureLayers.forEach((featureLayer, featureName) => {
        featureLayer.setStyle(featureName === selectedKelurahan
            ? kelurahanSelectedStyle
            : kelurahanNormalStyle);
    });
}

function selectKecamatan(selected) {
    kecamatanSelected = selected;
    kecamatanLayer.setStyle(kecamatanSelected ? kecamatanSelectedStyle : kecamatanNormalStyle);
}

const kecamatanLayer = L.geoJSON(null, {
    pane: MAP_KECAMATAN_PANE,
    style: () => kecamatanSelected ? kecamatanSelectedStyle : kecamatanNormalStyle,
    onEachFeature: (_feature, featureLayer) => {
        featureLayer.on('click', () => selectKecamatan(true));
    },
});

const kelurahanLayer = L.geoJSON(null, {
    pane: MAP_KELURAHAN_PANE,
    style: (feature) => feature.properties?.nama_kelurahan === selectedKelurahan
        ? kelurahanSelectedStyle
        : kelurahanNormalStyle,
    onEachFeature: (feature, featureLayer) => {
        const nama = feature.properties?.nama_kelurahan;
        if (!nama) return;

        kelurahanFeatureLayers.set(nama, featureLayer);
        featureLayer.on('click', () => selectKelurahan(nama));
    },
});

const overlays = {
    depot: layer,
    kesehatan: kesehatanLayer,
    sungai: sungaiLayer,
    kecamatan: kecamatanLayer,
    kelurahan: kelurahanLayer,
};

// Tampilkan layer yang dicentang pada kondisi awal.
document.querySelectorAll('.ov-check:checked').forEach((cb) => {
    overlays[cb.dataset.ov]?.addTo(map);
});

// Muat batas administrasi tanpa mengubah geometri GeoJSON sumber.
const layerLoadError = $('layer-load-error');
const mapElement = $('map');
const geoJSONLoadErrors = [];

async function loadGeoJSONLayer(layer, url, label, onLoaded = null) {
    try {
        const response = await fetch(url, {
            headers: { Accept: 'application/geo+json, application/json' },
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        const geoJSON = await response.json();
        if (!['Feature', 'FeatureCollection'].includes(geoJSON?.type)) {
            throw new Error('Respons bukan GeoJSON Feature atau FeatureCollection.');
        }

        layer.addData(geoJSON);
        onLoaded?.(geoJSON);
    } catch (error) {
        console.error(`Gagal memuat ${label}.`, error);
        geoJSONLoadErrors.push(`Informasi ${label} belum dapat dimuat. Periksa koneksi internet, lalu muat ulang halaman.`);
        layerLoadError.textContent = geoJSONLoadErrors.join(' ');
        layerLoadError.classList.remove('hidden');
        $('layer-panel').classList.remove('hidden');
    }
}

function populateKelurahanOptions(geoJSON) {
    const select = $('f-kel');
    const names = [...new Set(geoJSON.features
        .map((feature) => feature.properties?.nama_kelurahan)
        .filter((nama) => typeof nama === 'string' && nama.trim() !== ''))];

    select.replaceChildren(new Option('Semua kelurahan', ''));
    names.forEach((nama) => select.add(new Option(nama, nama)));
    selectKelurahan(selectedKelurahan);
}

loadGeoJSONLayer(
    kecamatanLayer,
    mapElement.dataset.batasKecamatanUrl,
    'batas Kecamatan Bogor Tengah',
);
loadGeoJSONLayer(
    kelurahanLayer,
    mapElement.dataset.batasKelurahanUrl,
    'batas kelurahan',
    populateKelurahanOptions,
);
loadGeoJSONLayer(
    sungaiLayer,
    mapElement.dataset.sungaiUrl,
    'jaringan sungai',
);
loadGeoJSONLayer(
    kesehatanLayer,
    mapElement.dataset.faskesUrl,
    'fasilitas kesehatan',
    () => {
        if (skippedFaskesFeatures > 0) {
            console.warn(`${skippedFaskesFeatures} fitur fasilitas kesehatan dilewati karena geometry Point atau koordinatnya tidak valid.`);
        }
    },
);

// Centang / hapus centang layer
document.querySelectorAll('.ov-check').forEach((cb) => {
    cb.addEventListener('change', () => {
        const l = overlays[cb.dataset.ov];
        if (cb.checked) {
            l.addTo(map);
        } else {
            if (cb.dataset.ov === 'kelurahan') selectKelurahan('');
            if (cb.dataset.ov === 'kecamatan') selectKecamatan(false);
            map.removeLayer(l);
        }
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
    const info = [jadwalText(d), d.antar && 'Layanan antar tersedia'].filter(Boolean).join(' / ');

    return `
      <div class="p-2">
        <div class="relative">
          <img src="${d.foto}" alt="${d.nama}" class="aspect-4/3 w-full rounded-xl object-cover">
          <button type="button" data-favorite-id="${d.id}" class="popup-fav absolute left-2 top-2 flex size-8 items-center justify-center rounded-full bg-white/90 text-slate-500 shadow" aria-label="Tambahkan ke favorit">
            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path stroke-linejoin="round" d="M12 21s-7.5-4.6-9.6-9.2C.9 8.4 2.7 5 6 5c2 0 3.3 1.1 4 2.2h4C14.7 6.1 16 5 18 5c3.3 0 5.1 3.4 3.6 6.8C19.5 16 12 21 12 21z"/>
            </svg>
          </button>
          <button type="button" class="popup-close absolute right-2 top-2 flex size-8 items-center justify-center rounded-full bg-white/90 text-slate-600 shadow transition hover:bg-white" aria-label="Tutup">
            <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/>
            </svg>
          </button>
        </div>

        <div class="px-1 pb-1 pt-3">
          <p class="text-base font-semibold text-brand-green-3">${d.nama}</p>
          ${info ? `<p class="mt-2 text-xs text-brand-green-2">${info}</p>` : ''}
          <p class="mt-1 text-xs text-slate-600">${jamText(d)}</p>
          <p class="mt-1 text-xs text-slate-500">${hariLiburText(d)}</p>
          <p class="mt-3 text-sm font-semibold text-brand-green-3">
            ${hargaText(d)}
          </p>
          <button type="button" data-id="${d.id}"
        class="popup-detail mt-4 block w-full rounded-full bg-brand-blue py-2.5 text-center text-sm font-medium text-white transition hover:brightness-95">
  Lihat detail depot
</button>
        </div>
      </div>`;
};

// ---------- Kartu detail ----------
const filterCard = $('filter-card');
const detailCard = $('detail-card');

const detailHtml = (d) => `
    <div class="flex items-center justify-between">
    <button type="button" class="detail-back flex items-center gap-1 text-xs text-brand-blue">
      <svg class="size-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M19 12H5M12 19l-7-7 7-7"/>
      </svg>
      Kembali ke hasil
    </button>
    <button type="button" data-favorite-id="${d.id}" aria-label="Tambahkan ke favorit"
            class="detail-fav fav-btn flex size-8 items-center justify-center rounded-full border border-slate-200 text-slate-500 transition hover:scale-105">
      <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path stroke-linejoin="round" d="M12 21s-7.5-4.6-9.6-9.2C.9 8.4 2.7 5 6 5c2 0 3.3 1.1 4 2.2h4C14.7 6.1 16 5 18 5c3.3 0 5.1 3.4 3.6 6.8C19.5 16.4 12 21 12 21z"/>
      </svg>
    </button>
  </div>

  <div class="relative mt-3">
    <img src="${d.foto}" alt="${d.nama}" class="aspect-16/10 w-full rounded-xl object-cover">
  </div>

  <h3 class="mt-3 text-lg font-semibold text-brand-green-3">${d.nama}</h3>

  <div class="mt-3 flex flex-wrap gap-2 text-[11px]">
    ${d.buka_setiap_hari
        ? '<span class="rounded-full bg-green-100 px-3 py-1 text-brand-green-2">Buka setiap hari</span>'
        : '<span class="rounded-full bg-slate-100 px-3 py-1 text-slate-600">Tidak buka setiap hari</span>'}
    ${d.antar ? '<span class="rounded-full bg-sky-100 px-3 py-1 text-brand-blue">Layanan antar</span>' : ''}
  </div>

  <p class="mt-4 text-sm font-semibold text-brand-green-3">${rupiah(d.harga_min)} – ${rupiah(d.harga_max)}</p>
  <p class="text-[11px] text-slate-500">per galon</p>

  <p class="mt-4 text-xs font-semibold text-brand-green-3">Alamat</p>
  <p class="mt-1 text-[11px] leading-relaxed text-slate-600">${d.alamat ?? '-'}</p>

  <p class="mt-4 text-xs text-brand-green-2">${jamText(d)}</p>
  <p class="mt-1 text-xs text-slate-500">${hariLiburText(d)}</p>
  <p class="mt-2 text-xs text-brand-green-3">${d.antar ? 'Layanan antar tersedia' : 'Layanan antar tidak tersedia'}</p>
  <a href="tel:${(d.telp ?? '').replace(/[^0-9+]/g, '')}" class="mt-2 block text-xs text-brand-blue">${d.telp ?? ''}</a>

  <div class="mt-4 grid grid-cols-2 gap-2">
    <a href="https://wa.me/${d.wa ?? ''}" target="_blank" rel="noopener"
       class="btn-popup rounded-full bg-brand-blue py-2.5 text-center text-xs font-medium text-white transition hover:brightness-95">
      Hubungi depot
    </a>
    <a href="https://www.google.com/maps/dir/?api=1&destination=${d.lat},${d.lng}" target="_blank" rel="noopener"
       class="rounded-full border border-brand-blue bg-white py-2.5 text-center text-xs font-medium text-brand-blue transition hover:bg-brand-blue/10">
      Petunjuk arah
    </a>
  </div>`;

function openDetail(d) {
    detailCard.innerHTML = detailHtml(d);
    syncFavoriteButtons(detailCard);
    filterCard.classList.add('hidden');
    detailCard.classList.remove('hidden');
    $('panel').classList.remove('hidden'); // supaya tampil juga di HP

    map.closePopup();
    map.flyTo([d.lat, d.lng], 16, { duration: 0.8 });

    detailCard.querySelector('.detail-back').addEventListener('click', closeDetail);
    detailCard.querySelector('.detail-fav').addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        toggleFavorite(d.id);
    });
}

function closeDetail() {
    detailCard.classList.add('hidden');
    filterCard.classList.remove('hidden');
}

// ---------- Panel favorit ----------
const favPanel = $('fav-panel');
const favList = $('fav-list');

function renderFav() {
    const items = getFavoriteIds().map((id) => depots.find((d) => String(d.id) === id)).filter(Boolean);

    $('fav-count').textContent = items.length;
    $('fav-jumlah').textContent = `${items.length} depot tersimpan`;
    favList.innerHTML = '';

    if (!items.length) {
        favList.innerHTML = `<li class="py-6 text-center text-xs leading-relaxed text-slate-500">
            Belum ada depot favorit.<br>Ketuk ikon hati untuk menyimpan depot.</li>`;
        return;
    }

    items.forEach((d) => {
        const li = document.createElement('li');
        li.innerHTML = `
          <button type="button" class="flex w-full items-center gap-3 rounded-xl border border-slate-200 p-2 text-left transition hover:bg-slate-50">
            <img src="${d.foto}" alt="${d.nama}" class="size-16 shrink-0 rounded-lg object-cover">
            <span class="min-w-0 flex-1">
              <span class="block truncate text-sm font-semibold text-brand-green-3">${d.nama}</span>
              <span class="mt-0.5 block text-[11px] text-brand-green-2">Lihat informasi depot</span>
            </span>
            <svg class="size-4 shrink-0 text-brand-blue" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 6l6 6-6 6"/>
            </svg>
          </button>`;
        li.firstElementChild.addEventListener('click', () => {
            favPanel.classList.add('hidden');
            openDetail(d);
        });
        favList.appendChild(li);
    });
}

$('fav-btn').addEventListener('click', () => favPanel.classList.toggle('hidden'));
$('fav-close').addEventListener('click', () => favPanel.classList.add('hidden'));
window.addEventListener('watr:favorites:change', renderFav);

renderFav();
// ---------- Elemen filter ----------
const fQ = $('f-q'), fKel = $('f-kel'), fHarga = $('f-harga');
const fAntar = $('f-antar'), fHarian = $('f-harian');
const listEl = $('hasil'), jumlahEl = $('hasil-jumlah'), hargaLabel = $('harga-label');
const wrapEl = $('hasil-wrap');
let listAktif = false; // daftar hasil tampil hanya setelah filter dipakai

fKel.addEventListener('change', () => selectKelurahan(fKel.value));

// ---------- Render ----------
function render() {
    const q = fQ.value.trim().toLowerCase();
    const kel = fKel.value;
    const maxHarga = Number(fHarga.value);

    const hasil = depots.filter((d) =>
    (!q || d.nama.toLowerCase().includes(q) || d.kelurahan.toLowerCase().includes(q) || String(d.alamat ?? '').toLowerCase().includes(q)) &&
    (!kel || d.kelurahan === kel) &&
    d.harga_min <= maxHarga &&
    (!fAntar.checked || d.antar) &&
    (!fHarian.checked || d.buka_setiap_hari)
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
            <img src="${d.foto}" alt="${d.nama}" class="w-20 shrink-0 object-cover">
            <span class="flex min-w-0 flex-col justify-center px-3 py-3">
              <span class="truncate text-sm font-medium text-brand-green-3">${d.nama}</span>
              <span class="mt-1 text-[11px] text-slate-500">${d.kelurahan}</span>
              <span class="mt-1 text-[11px] text-slate-500">${jamText(d)} · ${jadwalText(d)}</span>
              <span class="mt-1 text-[11px] text-slate-500">${hariLiburText(d)}</span>
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
        listEl.innerHTML = '<li class="text-xs text-slate-500">Tidak ditemukan depot yang cocok. Coba ubah pencarian atau filter.</li>';
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
    closeDetail();
    listAktif = fQ.value.trim() !== '';
    render();
});

fHarga.addEventListener('input', () => {
    hargaLabel.textContent = rupiah(Number(fHarga.value));
});

// Reset semua filter ke kondisi awal
$('f-reset').addEventListener('click', () => {
    fQ.value = '';
    selectKelurahan('');
    fHarga.value = fHarga.max;
    hargaLabel.textContent = rupiah(Number(fHarga.max));
    fAntar.checked = false;
    fHarian.checked = false;

    // Hapus ?q=... dari URL supaya tidak terbawa lagi
    history.replaceState(null, '', location.pathname);

    listAktif = false;
    render();
    map.flyTo(PUSAT, 14, { duration: 0.8 });
});

// Tombol close di popup
map.on('popupopen', (e) => {
    const el = e.popup.getElement();
    syncFavoriteButtons(el);
    el.querySelector('.popup-close')?.addEventListener('click', () => map.closePopup());
    el.querySelector('.popup-fav')?.addEventListener('click', (ev) => {
        ev.preventDefault();
        ev.stopPropagation();
        toggleFavorite(ev.currentTarget.dataset.favoriteId);
    });
    el.querySelector('.popup-detail')?.addEventListener('click', (ev) => {
        const d = depots.find((x) => x.id === Number(ev.currentTarget.dataset.id));
        if (d) openDetail(d);
    });
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

const depotId = Number(new URLSearchParams(location.search).get('depot'));
if (depotId) {
    const d = depots.find((x) => x.id === depotId);
    if (d) openDetail(d);
}

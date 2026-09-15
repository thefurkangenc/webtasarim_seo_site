/**
 * <div data-map-picker> harita alanını Google Maps ile sürer.
 *
 * Layout'ta yüklenir; sayfa JS'inin çağırmasına gerek yoktur. Haritaya
 * tıklayınca veya işareti sürükleyince enlem/boylam input'ları dolar.
 * API anahtarı yoksa bileşen haritayı render etmez, bu dosya no-op kalır.
 */

let mapsLoader = null;

function loadGoogleMaps(apiKey) {
    if (window.google?.maps?.Map) {
        return Promise.resolve(window.google.maps);
    }

    if (mapsLoader) {
        return mapsLoader;
    }

    mapsLoader = new Promise((resolve, reject) => {
        const callback = '__initGoogleMapsPicker';

        window[callback] = () => {
            delete window[callback];
            resolve(window.google.maps);
        };

        const script = document.createElement('script');
        // `loading=async` verilmezse Google konsola "suboptimal performance"
        // uyarısı basıyor (script zaten async yükleniyor, API bunu parametreden de bekliyor).
        script.src = `https://maps.googleapis.com/maps/api/js?key=${encodeURIComponent(apiKey)}&callback=${callback}&language=tr&region=TR&loading=async`;
        script.async = true;
        script.onerror = () => reject(new Error('Google Maps yüklenemedi.'));
        document.head.appendChild(script);
    });

    return mapsLoader;
}

function parseCoord(value) {
    if (value === null || value === undefined || value === '') {
        return null;
    }

    const number = Number(value);

    return Number.isFinite(number) ? number : null;
}

function bindPicker(root, maps) {
    if (root.dataset.mapReady === '1') {
        return;
    }

    root.dataset.mapReady = '1';

    const canvas = root.querySelector('[data-map-canvas]');
    const latInput = root.querySelector('[data-map-lat]');
    const lngInput = root.querySelector('[data-map-lng]');

    if (! canvas || ! latInput || ! lngInput) {
        return;
    }

    const defaultLat = Number(root.dataset.defaultLat);
    const defaultLng = Number(root.dataset.defaultLng);
    const defaultZoom = Number(root.dataset.defaultZoom);
    const selectedZoom = Number(root.dataset.selectedZoom);

    const lat = parseCoord(latInput.value);
    const lng = parseCoord(lngInput.value);
    const hasPoint = lat !== null && lng !== null;

    const map = new maps.Map(canvas, {
        center: {
            lat: hasPoint ? lat : defaultLat,
            lng: hasPoint ? lng : defaultLng,
        },
        zoom: hasPoint ? selectedZoom : defaultZoom,
        mapTypeControl: false,
        streetViewControl: false,
        fullscreenControl: false,
        clickableIcons: false,
    });

    let marker = null;

    const setPoint = (position, pan = true) => {
        latInput.value = position.lat.toFixed(7);
        lngInput.value = position.lng.toFixed(7);

        if (! marker) {
            marker = new maps.Marker({
                map,
                position,
                draggable: true,
            });

            marker.addListener('dragend', () => {
                const next = marker.getPosition();
                setPoint({ lat: next.lat(), lng: next.lng() }, false);
            });
        } else {
            marker.setPosition(position);
        }

        if (pan) {
            map.panTo(position);

            if (map.getZoom() < selectedZoom) {
                map.setZoom(selectedZoom);
            }
        }
    };

    if (hasPoint) {
        setPoint({ lat, lng }, false);
    }

    map.addListener('click', (event) => {
        setPoint({ lat: event.latLng.lat(), lng: event.latLng.lng() });
    });

    const onManualChange = () => {
        const nextLat = parseCoord(latInput.value);
        const nextLng = parseCoord(lngInput.value);

        if (nextLat === null || nextLng === null) {
            return;
        }

        setPoint({ lat: nextLat, lng: nextLng });
    };

    latInput.addEventListener('change', onManualChange);
    lngInput.addEventListener('change', onManualChange);
}

function init(root = document) {
    const pickers = [...(root.querySelectorAll?.('[data-map-picker]') ?? [])]
        .filter((picker) => picker.dataset.apiKey && picker.dataset.mapReady !== '1');

    if (pickers.length === 0) {
        return;
    }

    loadGoogleMaps(pickers[0].dataset.apiKey)
        .then((maps) => pickers.forEach((picker) => bindPicker(picker, maps)))
        .catch(() => {
            pickers.forEach((picker) => {
                const canvas = picker.querySelector('[data-map-canvas]');

                if (! canvas) {
                    return;
                }

                canvas.replaceChildren();
                canvas.classList.add('flex', 'items-center', 'justify-center');

                const message = document.createElement('p');
                message.className = 'text-sm text-gray-500 dark:text-gray-400';
                message.textContent = 'Harita yüklenemedi. API anahtarını kontrol edin.';
                canvas.append(message);
            });
        });
}

document.addEventListener('DOMContentLoaded', () => init());
document.addEventListener('admin:content-loaded', (event) => init(event.target));

export { init as initMapPickers };

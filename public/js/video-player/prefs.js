/**
 * Ses, sessiz, hız ve kalite tercihleri. Videoya göre değil, sitede ortak.
 */

const KEYS = {
    volume: 'player:volume',
    muted: 'player:muted',
    rate: 'player:rate',
    quality: 'player:quality',
};

function read(key, fallback) {
    try {
        const value = localStorage.getItem(key);

        return value === null ? fallback : value;
    } catch {
        return fallback;
    }
}

function write(key, value) {
    try {
        localStorage.setItem(key, String(value));
    } catch {
        // gizli mod / kota
    }
}

export const prefs = {
    volume() {
        const value = Number(read(KEYS.volume, '1'));

        return Number.isFinite(value) ? Math.min(1, Math.max(0, value)) : 1;
    },

    setVolume(value) {
        write(KEYS.volume, value);
    },

    muted() {
        return read(KEYS.muted, '0') === '1';
    },

    setMuted(value) {
        write(KEYS.muted, value ? '1' : '0');
    },

    rate() {
        const value = Number(read(KEYS.rate, '1'));

        return Number.isFinite(value) && value > 0 ? value : 1;
    },

    setRate(value) {
        write(KEYS.rate, value);
    },

    quality() {
        return read(KEYS.quality, '');
    },

    setQuality(value) {
        write(KEYS.quality, value);
    },
};

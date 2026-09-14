/**
 * Süre çubuğu hover karesi — WebVTT + sprite.
 *
 * Cue metni `xywh=x,y,w,h`. Sprite yoksa çağıran yalnızca zaman basar.
 */

export function parseSpriteVtt(text) {
    const cues = [];
    const blocks = String(text).replace(/^\uFEFF/, '').split(/\n\s*\n/);

    for (const block of blocks) {
        const lines = block.trim().split('\n');
        const time = lines.find((line) => line.includes('-->'));

        if (! time) {
            continue;
        }

        const [startRaw, endRaw] = time.split('-->').map((part) => part.trim());
        const xywh = lines.map((line) => line.match(/xywh=(\d+),(\d+),(\d+),(\d+)/i)).find(Boolean);

        if (! xywh) {
            continue;
        }

        cues.push({
            start: parseTimestamp(startRaw),
            end: parseTimestamp(endRaw),
            x: Number(xywh[1]),
            y: Number(xywh[2]),
            w: Number(xywh[3]),
            h: Number(xywh[4]),
        });
    }

    return cues;
}

export function cueAt(cues, seconds) {
    return cues.find((cue) => seconds >= cue.start && seconds < cue.end) ?? cues.at(-1) ?? null;
}

function parseTimestamp(value) {
    const parts = String(value).trim().split(':').map(Number);

    if (parts.some((part) => Number.isNaN(part))) {
        return 0;
    }

    if (parts.length === 3) {
        return parts[0] * 3600 + parts[1] * 60 + parts[2];
    }

    if (parts.length === 2) {
        return parts[0] * 60 + parts[1];
    }

    return parts[0] ?? 0;
}

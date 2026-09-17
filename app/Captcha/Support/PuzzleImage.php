<?php

namespace App\Captcha\Support;

use GdImage;

/**
 * Yapboz görsellerini üretir.
 *
 * Arka plan her seferinde koddan çizilir (rastgele degrade + lekeler) — hazır
 * görsel klasörü yok, bu yüzden klasör başka bir projeye kopyalandığında
 * yanına dosya taşımak gerekmez.
 *
 * Parça ve boşluk AYNI geometriden türetilir: parçanın piksel piksel kopyası
 * alınır, aynı alan arka planda karartılır. Doğru konum yalnızca sunucuda
 * kalır; tarayıcıya giden iki görselde de koordinat bilgisi yoktur.
 */
final class PuzzleImage
{
    /** Tırnak/çentik yarıçapı. */
    private int $knob;

    public function __construct(
        private readonly int $width,
        private readonly int $height,
        private readonly int $size,
    ) {
        $this->knob = max(6, (int) round($this->size * 0.22));
    }

    /** @return array{background: string, piece: string, x: int, y: int} */
    public function generate(): array
    {
        $background = $this->paintBackground();

        // Boşluk sola çok yakın olursa kullanıcı hiç kaydırmadan doğru
        // konumda kalır; sol kenardan en az genişliğin üçte biri uzakta.
        $x = random_int((int) round($this->width * 0.34), $this->width - $this->size - 8);
        $y = random_int(8, $this->height - $this->size - 8);

        $piece = $this->cutPiece($background, $x, $y);
        $this->punchHole($background, $x, $y);

        return [
            'background' => $this->encode($background),
            'piece' => $this->encode($piece),
            'x' => $x,
            'y' => $y,
        ];
    }

    private function paintBackground(): GdImage
    {
        $image = imagecreatetruecolor($this->width, $this->height);
        imagealphablending($image, true);

        $hue = random_int(0, 359);
        $shift = random_int(35, 140) * (random_int(0, 1) ? 1 : -1);

        for ($row = 0; $row < $this->height; $row++) {
            $t = $row / max(1, $this->height - 1);
            [$r, $g, $b] = $this->hsl($hue + $shift * $t, 0.46, 0.60 - 0.22 * $t);
            imageline($image, 0, $row, $this->width, $row, imagecolorallocate($image, $r, $g, $b));
        }

        for ($i = 0, $blobs = random_int(5, 8); $i < $blobs; $i++) {
            [$r, $g, $b] = $this->hsl($hue + random_int(-80, 80), random_int(35, 75) / 100, random_int(30, 80) / 100);
            $radius = random_int(26, 78);

            imagefilledellipse(
                $image,
                random_int(0, $this->width),
                random_int(0, $this->height),
                $radius * 2,
                $radius * 2,
                imagecolorallocatealpha($image, $r, $g, $b, random_int(82, 108)),
            );
        }

        for ($i = 0, $stripes = random_int(2, 4); $i < $stripes; $i++) {
            [$r, $g, $b] = $this->hsl($hue + random_int(-40, 40), 0.5, random_int(25, 85) / 100);
            imagesetthickness($image, random_int(6, 16));
            imageline(
                $image,
                random_int(-40, $this->width),
                -20,
                random_int(0, $this->width + 40),
                $this->height + 20,
                imagecolorallocatealpha($image, $r, $g, $b, random_int(95, 115)),
            );
        }

        imagesetthickness($image, 1);
        imagealphablending($image, false);

        return $image;
    }

    private function cutPiece(GdImage $background, int $x, int $y): GdImage
    {
        $piece = imagecreatetruecolor($this->size, $this->size);
        imagealphablending($piece, false);
        imagesavealpha($piece, true);
        imagefilledrectangle($piece, 0, 0, $this->size, $this->size, 127 << 24);

        foreach ($this->shape() as [$px, $py, $cover, $rim]) {
            $rgb = imagecolorat($background, $x + $px, $y + $py);

            // Kenara doğru beyaza kaçan ince bir pay: parçanın sınırını
            // arka planın üzerinde görünür kılar.
            [$r, $g, $b] = $this->mix($rgb, 255, 255, 255, $rim * 0.8);

            imagesetpixel($piece, $px, $py, ((127 - (int) round($cover * 127)) << 24) | ($r << 16) | ($g << 8) | $b);
        }

        return $piece;
    }

    private function punchHole(GdImage $background, int $x, int $y): void
    {
        foreach ($this->shape() as [$px, $py, $cover, $rim]) {
            $rgb = imagecolorat($background, $x + $px, $y + $py);

            [$r, $g, $b] = $this->mix($rgb, 0, 0, 0, $cover * 0.62);
            [$r, $g, $b] = $this->mix(($r << 16) | ($g << 8) | $b, 255, 255, 255, $rim * 0.55);

            imagesetpixel($background, $x + $px, $y + $py, ($r << 16) | ($g << 8) | $b);
        }
    }

    /**
     * Parça alanındaki her pikselin kapsama oranı ve kenar payı.
     *
     * @return \Generator<int, array{int, int, float, float}>
     */
    private function shape(): \Generator
    {
        for ($px = 0; $px < $this->size; $px++) {
            for ($py = 0; $py < $this->size; $py++) {
                $cover = $this->coverage($px, $py, 0.0);

                if ($cover <= 0.02) {
                    continue;
                }

                yield [$px, $py, $cover, max(0.0, $cover - $this->coverage($px, $py, 1.7))];
            }
        }
    }

    /** Kenarları tırtıklı çıkmasın diye 3x3 örnekleme. */
    private function coverage(int $px, int $py, float $inset): float
    {
        $hit = 0;

        for ($i = 0; $i < 3; $i++) {
            for ($j = 0; $j < 3; $j++) {
                if ($this->inside($px + ($i + 0.5) / 3, $py + ($j + 0.5) / 3, $inset)) {
                    $hit++;
                }
            }
        }

        return $hit / 9;
    }

    /**
     * Klasik yapboz parçası: taban kare, sağ kenarda çıkıntı, üst kenarda
     * çentik. `$inset` şekli içeri doğru daraltır (kenar payını bulmak için).
     */
    private function inside(float $x, float $y, float $inset): bool
    {
        $base = $this->size - $this->knob;

        if ($this->within($x, $y, $base / 2, 0, $this->knob + $inset)) {
            return false;
        }

        if ($this->within($x, $y, $base, $this->size / 2, $this->knob - $inset)) {
            return true;
        }

        return $this->inRoundedRect($x, $y, $base, $inset);
    }

    private function within(float $x, float $y, float $cx, float $cy, float $radius): bool
    {
        return $radius > 0 && (($x - $cx) ** 2 + ($y - $cy) ** 2) <= $radius ** 2;
    }

    private function inRoundedRect(float $x, float $y, float $base, float $inset): bool
    {
        $x1 = $base - $inset;
        $y1 = $this->size - $inset;

        if ($x < $inset || $x > $x1 || $y < $inset || $y > $y1) {
            return false;
        }

        $radius = max(0.0, 6.0 - $inset);

        if ($radius <= 0) {
            return true;
        }

        $cx = min(max($x, $inset + $radius), $x1 - $radius);
        $cy = min(max($y, $inset + $radius), $y1 - $radius);

        return (($x - $cx) ** 2 + ($y - $cy) ** 2) <= $radius ** 2;
    }

    /** @return array{int, int, int} */
    private function mix(int $rgb, int $r2, int $g2, int $b2, float $amount): array
    {
        $amount = min(1.0, max(0.0, $amount));

        return [
            (int) round((($rgb >> 16) & 0xFF) * (1 - $amount) + $r2 * $amount),
            (int) round((($rgb >> 8) & 0xFF) * (1 - $amount) + $g2 * $amount),
            (int) round(($rgb & 0xFF) * (1 - $amount) + $b2 * $amount),
        ];
    }

    /** @return array{int, int, int} */
    private function hsl(float $hue, float $saturation, float $lightness): array
    {
        $hue = fmod(fmod($hue, 360) + 360, 360) / 360;
        $lightness = min(1.0, max(0.0, $lightness));

        if ($saturation <= 0) {
            $value = (int) round($lightness * 255);

            return [$value, $value, $value];
        }

        $q = $lightness < 0.5
            ? $lightness * (1 + $saturation)
            : $lightness + $saturation - $lightness * $saturation;
        $p = 2 * $lightness - $q;

        return [
            (int) round($this->channel($p, $q, $hue + 1 / 3) * 255),
            (int) round($this->channel($p, $q, $hue) * 255),
            (int) round($this->channel($p, $q, $hue - 1 / 3) * 255),
        ];
    }

    private function channel(float $p, float $q, float $t): float
    {
        $t = fmod(fmod($t, 1) + 1, 1);

        return match (true) {
            $t < 1 / 6 => $p + ($q - $p) * 6 * $t,
            $t < 1 / 2 => $q,
            $t < 2 / 3 => $p + ($q - $p) * (2 / 3 - $t) * 6,
            default => $p,
        };
    }

    private function encode(GdImage $image): string
    {
        ob_start();
        imagepng($image, null, 8);
        $data = (string) ob_get_clean();
        imagedestroy($image);

        return 'data:image/png;base64,'.base64_encode($data);
    }
}

<?php

namespace App\Captcha\Drivers;

use App\Captcha\CaptchaManager;
use App\Captcha\Contracts\Driver;
use App\Captcha\Support\Challenge;
use App\Captcha\Support\PuzzleImage;

/**
 * Kaydırmalı yapboz. Kullanıcı eksik parçayı boşluğa oturtur.
 *
 * Doğru konum tarayıcıya hiç gitmez: iki görsel de koordinat taşımaz, cevap
 * yalnızca imzalı jetonun içindeki `x` ile karşılaştırılır. Her jeton tek
 * denemede tükendiği için 320 pikselin içinde deneme yanılma yapılamaz.
 */
class PuzzleDriver implements Driver
{
    public function __construct(private readonly CaptchaManager $manager) {}

    public function key(): string
    {
        return 'puzzle';
    }

    public function view(): string
    {
        return 'captcha::drivers.puzzle';
    }

    public function challenge(): Challenge
    {
        $width = (int) $this->option('width', 320);
        $height = (int) $this->option('height', 180);
        $size = (int) $this->option('piece', 54);

        $puzzle = (new PuzzleImage($width, $height, $size))->generate();

        return new Challenge(
            payload: [
                'background' => $puzzle['background'],
                'piece' => $puzzle['piece'],
                'width' => $width,
                'height' => $height,
                'size' => $size,
                'top' => $puzzle['y'],
            ],
            secret: [
                'x' => $puzzle['x'],
                'issued_at' => now()->getTimestampMs(),
            ],
        );
    }

    public function solved(array $answer, array $secret): bool
    {
        $duration = (int) ($answer['duration'] ?? 0);

        // Sürükleme insan hızında mı? Anında biten ya da tek sıçramada
        // tamamlanan hareket otomasyon sayılır.
        if ($duration < (int) $this->option('min_duration', 250)
            || $duration > (int) $this->option('max_duration', 120000)
            || (int) ($answer['moves'] ?? 0) < (int) $this->option('min_moves', 4)) {
            return false;
        }

        return abs((float) ($answer['answer'] ?? -1) - (float) ($secret['x'] ?? -1))
            <= (float) $this->option('tolerance', 6);
    }

    public function rules(): array
    {
        return [
            'answer' => ['required', 'numeric', 'min:0', 'max:4000'],
            'duration' => ['required', 'integer', 'min:0', 'max:3600000'],
            'moves' => ['required', 'integer', 'min:0', 'max:100000'],
        ];
    }

    private function option(string $key, mixed $default): mixed
    {
        return $this->manager->setting('puzzle.'.$key, $default);
    }
}

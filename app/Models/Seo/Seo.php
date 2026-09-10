<?php

namespace App\Models\Seo;

use App\Models\Media\Media;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Table('seo')]
#[Fillable([
    'meta_title', 'meta_description', 'meta_keywords', 'canonical_url',
    'robots_index', 'robots_follow', 'og_media_id',
    'schema_type', 'schema_json', 'schema_override',
    'focus_keyword', 'seo_score', 'readability_score', 'score_checks', 'analyzed_at',
])]
class Seo extends Model
{
    /** Skor renk/etiket eşiği — config/seo.php `grade` ile aynı. */
    public const GRADES = [
        'bad' => ['label' => 'Kötü', 'color' => 'danger'],
        'ok' => ['label' => 'İyileştirilebilir', 'color' => 'warning'],
        'good' => ['label' => 'İyi', 'color' => 'success'],
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'robots_index' => 'boolean',
            'robots_follow' => 'boolean',
            'schema_json' => 'array',
            'schema_override' => 'boolean',
            'seo_score' => 'integer',
            'readability_score' => 'integer',
            'score_checks' => 'array',
            'analyzed_at' => 'datetime',
        ];
    }

    /** Skoru nota çevirir: bad / ok / good. */
    public function scoreGrade(): ?string
    {
        if ($this->seo_score === null) {
            return null;
        }

        $grade = config('seo.grade');

        return match (true) {
            $this->seo_score <= $grade['bad'] => 'bad',
            $this->seo_score <= $grade['ok'] => 'ok',
            default => 'good',
        };
    }

    public function seoable(): MorphTo
    {
        return $this->morphTo();
    }

    public function ogMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'og_media_id');
    }

    /** Arama motorlarına verilecek robots direktifi. */
    public function robots(): string
    {
        return ($this->robots_index ? 'index' : 'noindex').','.($this->robots_follow ? 'follow' : 'nofollow');
    }
}

<?php

namespace App\Services\Faq;

use App\Models\Faq\Faq;
use App\Services\Concerns\ReordersRecords;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class FaqService
{
    use ReordersRecords;

    public function list(array $filters): LengthAwarePaginator
    {
        return Faq::query()
            ->when($filters['search'] ?? null, fn ($query, $term) => $query->where(
                fn ($query) => $query->where('question', 'like', "%{$term}%")
                    ->orWhere('answer', 'like', "%{$term}%")
            ))
            ->orderBy($filters['sort'] ?? 'sort_order', $filters['direction'] ?? 'asc')
            ->paginate($filters['per_page'] ?? 15)
            ->through(fn (Faq $faq) => $faq->toPayload());
    }

    /** @return array<string, mixed> */
    public function formData(?Faq $faq): array
    {
        return ['faq' => $faq];
    }

    public function create(array $data): Faq
    {
        return Faq::create($this->attributes($data));
    }

    public function update(Faq $faq, array $data): Faq
    {
        $faq->update($this->attributes($data));

        return $faq;
    }

    public function delete(Faq $faq): void
    {
        $faq->delete();
    }

    /** Ön yüz için: tüm soruları sıralarıyla döndürür. */
    public function active(): Collection
    {
        return Faq::query()
            ->orderBy('sort_order')
            ->get();
    }

    /** @return array<string, mixed> */
    private function attributes(array $data): array
    {
        return [
            'question' => $data['question'],
            'answer' => $data['answer'],
        ];
    }

    protected function reorderModel(): string
    {
        return Faq::class;
    }
}

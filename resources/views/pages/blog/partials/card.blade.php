{{-- $blog App\Models\Blog\Blog --}}
@php
    $cardUrl = $blog->publicUrl();
    $cardCover = $blog->getFirstMedia('cover');
    $cardDate = $blog->published_at ?? $blog->created_at;
@endphp

<article class="blog-card">
    <a href="{{ $cardUrl }}" class="blog-card__media" tabindex="-1" aria-hidden="true">
        @if ($cardCover)
            <img src="{{ $cardCover->url('medium') }}" alt="{{ $blog->title }}" loading="lazy">
        @else
            <span class="blog-card__media-placeholder" aria-hidden="true"></span>
        @endif
        @if ($cardDate)
            <time class="blog-card__date" datetime="{{ $cardDate->toDateString() }}">
                {{ \App\Support\DateFormat::short($cardDate) }}
            </time>
        @endif
    </a>

    <div class="blog-card__body">
        @if ($blog->category)
            <span class="blog-card__category">{{ $blog->category->name }}</span>
        @endif

        <h2 class="blog-card__title">
            <a href="{{ $cardUrl }}">{{ $blog->title }}</a>
        </h2>

        @if (filled($blog->excerpt))
            <p class="blog-card__excerpt">{{ Str::limit($blog->excerpt, 120) }}</p>
        @endif

        <div class="blog-card__foot">
            @if ($blog->author)
                <span class="blog-card__author">{{ $blog->author->name }}</span>
            @endif
            <a href="{{ $cardUrl }}" class="blog-card__link">
                Devamını oku <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
            </a>
        </div>
    </div>
</article>

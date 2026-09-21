{{-- $blog App\Models\Blog\Blog --}}
@php
    $sidebarDate = $blog->published_at ?? $blog->created_at;
    $shareUrl = urlencode((string) $blog->publicUrl());
    $shareTitle = urlencode($blog->title);
@endphp

<aside class="blog-detail-sidebar" aria-label="Yazı bilgileri">
    <div class="blog-detail-sidebar__card">
        <h2 class="blog-detail-sidebar__title">Yazı Bilgileri</h2>
        <ul class="blog-detail-sidebar__list">
            @if ($sidebarDate)
                <li>
                    <span class="blog-detail-sidebar__icon" aria-hidden="true"><i class="fa-regular fa-calendar"></i></span>
                    <span class="blog-detail-sidebar__text">
                        <span class="blog-detail-sidebar__label">Yayın tarihi</span>
                        <time datetime="{{ $sidebarDate->toDateString() }}">{{ \App\Support\DateFormat::long($sidebarDate) }}</time>
                    </span>
                </li>
            @endif
            @if ($blog->category)
                <li>
                    <span class="blog-detail-sidebar__icon" aria-hidden="true"><i class="fa-regular fa-folder"></i></span>
                    <span class="blog-detail-sidebar__text">
                        <span class="blog-detail-sidebar__label">Kategori</span>
                        @if ($blog->category->publicUrl())
                            <a href="{{ $blog->category->publicUrl() }}">{{ $blog->category->name }}</a>
                        @else
                            <span>{{ $blog->category->name }}</span>
                        @endif
                    </span>
                </li>
            @endif
            @if (isset($readingMinutes))
                <li>
                    <span class="blog-detail-sidebar__icon" aria-hidden="true"><i class="fa-regular fa-clock"></i></span>
                    <span class="blog-detail-sidebar__text">
                        <span class="blog-detail-sidebar__label">Okuma süresi</span>
                        <span>{{ $readingMinutes }} dk</span>
                    </span>
                </li>
            @endif
        </ul>
    </div>

    @if ($blog->tags->isNotEmpty())
        <div class="blog-detail-sidebar__card">
            <h2 class="blog-detail-sidebar__title">Etiketler</h2>
            <div class="blog-taxonomy-cloud">
                @foreach ($blog->tags as $sidebarTag)
                    <a href="{{ route('blog.etiket', $sidebarTag->slug) }}">#{{ $sidebarTag->name }}</a>
                @endforeach
            </div>
        </div>
    @endif

    <div class="blog-detail-sidebar__card blog-detail-sidebar__share">
        <h2 class="blog-detail-sidebar__title">Paylaş</h2>
        <div class="blog-detail-share">
            <a href="https://www.facebook.com/sharer/sharer.php?u={{ $shareUrl }}" target="_blank" rel="noopener noreferrer" aria-label="Facebook'ta paylaş">
                <i class="fa-brands fa-facebook-f" aria-hidden="true"></i>
            </a>
            <a href="https://twitter.com/intent/tweet?url={{ $shareUrl }}&text={{ $shareTitle }}" target="_blank" rel="noopener noreferrer" aria-label="X'te paylaş">
                <i class="fa-brands fa-x-twitter" aria-hidden="true"></i>
            </a>
            <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ $shareUrl }}" target="_blank" rel="noopener noreferrer" aria-label="LinkedIn'de paylaş">
                <i class="fa-brands fa-linkedin-in" aria-hidden="true"></i>
            </a>
            <a href="https://wa.me/?text={{ urlencode($blog->title . ' ' . $blog->publicUrl()) }}" target="_blank" rel="noopener noreferrer" aria-label="WhatsApp'ta paylaş">
                <i class="fa-brands fa-whatsapp" aria-hidden="true"></i>
            </a>
        </div>
    </div>

    <div class="blog-detail-sidebar__cta">
        <h2 class="blog-detail-sidebar__cta-title">Projeniz için destek</h2>
        <p>Web tasarım, SEO veya dijital pazarlama konusunda yardıma mı ihtiyacınız var? Ekibimiz size özel çözüm önerir.</p>
        <a class="ui-btn ui-btn--solid ui-btn--light" href="{{ route('iletisim') }}">
            Teklif Alın <i class="fa-solid fa-arrow-right"></i>
        </a>
    </div>

    <a href="{{ route('blog') }}" class="blog-detail-sidebar__back">
        <i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Tüm yazılara dön
    </a>
</aside>

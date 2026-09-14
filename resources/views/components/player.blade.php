@props([
    'media' => null,
    'embed' => null,
    'title' => '',
    'compact' => false,
])

@php
    $embedUrl = is_array($embed) ? ($embed['embed_url'] ?? null) : null;
@endphp

@if ($embedUrl)
    <div class="ratio ratio-16x9">
        <iframe src="{{ $embedUrl }}" title="{{ $title }}" loading="lazy" allowfullscreen
            allow="accelerometer; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
            referrerpolicy="strict-origin-when-cross-origin"></iframe>
    </div>
@elseif ($media)
    @once
        @push('css')
            <link rel="stylesheet" href="{{ asset('assets/css/video-player.css') }}">
        @endpush
        @push('scripts')
            <script type="module" src="{{ asset('assets/js/video-player.js') }}"></script>
        @endpush
    @endonce

    <div class="vp" data-player data-player-icons="fa" @if ($compact) data-player-compact @endif>
        <script type="application/json" data-player-config>{!! json_encode(
            [...($media->playerPayload() ?? []), 'title' => $title],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP
        ) !!}</script>
    </div>
@endif

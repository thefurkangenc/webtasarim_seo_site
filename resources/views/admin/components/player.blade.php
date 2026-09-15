@props([
    'media' => null,
    'embed' => null,
    'title' => '',
    'compact' => false,
    'poster' => null,
])

@php
    $embedUrl = is_array($embed) ? ($embed['embed_url'] ?? null) : null;
    $payload = [...($media?->playerPayload() ?? []), 'title' => $title];

    if (empty($payload['poster']) && $poster) {
        $payload['poster'] = $poster;
    }
@endphp

@if ($embedUrl)
    <div class="aspect-video overflow-hidden rounded-md bg-black">
        <iframe src="{{ $embedUrl }}" title="{{ $title }}" class="h-full w-full" loading="lazy" allowfullscreen
            allow="accelerometer; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
            referrerpolicy="strict-origin-when-cross-origin"></iframe>
    </div>
@elseif ($media)
    @once
        @push('admin.css')
            <link rel="stylesheet" href="{{ asset('assets/admin/css/video-player.css') }}">
        @endpush
        @push('admin.scripts')
            <script type="module" src="{{ asset('assets/admin/js/core/video-player.js') }}"></script>
        @endpush
    @endonce

    <div class="vp" data-player data-player-icons="material" @if ($compact) data-player-compact @endif>
        <script type="application/json" data-player-config>{!! json_encode(
            $payload,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP
        ) !!}</script>
    </div>
@endif

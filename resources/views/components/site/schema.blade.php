@props([
    'context' => null,
])

@php
    // Bileşen kendi kapsamındadır; layout `$schemaContext`'i :context ile geçirir.
    // Boşsa (controller vermediyse) route adından türetilir.
    $schemaGraph = app(\App\Services\Schema\SchemaGraphBuilder::class)
        ->build($context ?? \App\Support\SchemaContext::fromRoute());
@endphp

@if (! empty($schemaGraph['@graph']))
    <script type="application/ld+json">
    {!! json_encode($schemaGraph, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
    </script>
@endif

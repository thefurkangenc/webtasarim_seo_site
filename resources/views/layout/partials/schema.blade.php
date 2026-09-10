@php
    // Dinamik controller'lar $schemaContext'i açıkça geçirir (hizmet, blog,
    // dinamik sayfa); statik route'lar route adından türetilir.
    $schemaGraph = app(\App\Services\Schema\SchemaGraphBuilder::class)
        ->build($schemaContext ?? \App\Support\SchemaContext::fromRoute());
@endphp

@if (! empty($schemaGraph['@graph']))
    <script type="application/ld+json">
{!! json_encode($schemaGraph, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
    </script>
@endif

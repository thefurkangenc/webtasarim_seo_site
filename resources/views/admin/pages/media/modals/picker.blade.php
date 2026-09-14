{{-- Form içinden açılan seçici modalın gövdesi.
     $accept controller'dan gelir (?accept=image); null ise her tür listelenir. --}}
@include('admin.pages.media.partials.browser', [
    'selectable' => true,
    'manageable' => true,
    'accept' => $accept ?? null,
])

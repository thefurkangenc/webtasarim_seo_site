{{-- Form içinden açılan seçici modalın gövdesi. --}}
@include('admin.pages.media.partials.browser', [
    'folders' => $folders,
    'selectable' => true,
    'manageable' => true,
])

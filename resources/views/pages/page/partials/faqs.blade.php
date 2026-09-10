{{--
    Sayfaya bağlanmış sorular. Hiç soru bağlanmadıysa blok hiç basılmaz.
    Akordeon kimlikleri sayfa kimliğiyle önekli: aynı soru birden fazla
    içeriğe bağlanabildiği için sayfa içinde tekil olmaları gerekiyor.
--}}
@if ($page->faqs->isNotEmpty())
    <div class="sp page-faq">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 m-auto">
                    <div class="research-faq">
                        <h3>Sıkça Sorulan Sorular</h3>

                        <div class="accordion accordion1" id="page-faq-{{ $page->id }}">
                            @foreach ($page->faqs as $index => $faq)
                                @php($target = "page-{$page->id}-faq-{$faq->id}")

                                <div class="accordion-item {{ $index === 0 ? 'active' : '' }}">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button {{ $index === 0 ? '' : 'collapsed' }}"
                                            type="button" data-bs-toggle="collapse" data-bs-target="#{{ $target }}"
                                            aria-expanded="{{ $index === 0 ? 'true' : 'false' }}"
                                            aria-controls="{{ $target }}">
                                            {{ $faq->question }}
                                        </button>
                                    </h2>
                                    <div id="{{ $target }}"
                                        class="accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}"
                                        data-bs-parent="#page-faq-{{ $page->id }}">
                                        <div class="accordion-body">
                                            {!! $faq->answer !!}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

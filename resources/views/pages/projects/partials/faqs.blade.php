{{--
    Projeye bağlanmış sorular. Akordeon kimlikleri proje kimliğiyle önekli:
    aynı soru birden fazla içeriğe bağlanabildiği için sayfa içinde tekil
    olmaları gerekiyor.
--}}
@if ($project->faqs->isNotEmpty())
    <div class="research-faq mt-50">
        <h3>Sıkça Sorulan Sorular</h3>

        <div class="accordion accordion1" id="project-faq-{{ $project->id }}">
            @foreach ($project->faqs as $index => $faq)
                @php($target = "project-{$project->id}-faq-{$faq->id}")

                <div class="accordion-item {{ $index === 0 ? 'active' : '' }}">
                    <h2 class="accordion-header">
                        <button class="accordion-button {{ $index === 0 ? '' : 'collapsed' }}" type="button"
                            data-bs-toggle="collapse" data-bs-target="#{{ $target }}"
                            aria-expanded="{{ $index === 0 ? 'true' : 'false' }}" aria-controls="{{ $target }}">
                            {{ $faq->question }}
                        </button>
                    </h2>
                    <div id="{{ $target }}" class="accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}"
                        data-bs-parent="#project-faq-{{ $project->id }}">
                        <div class="accordion-body">
                            {!! $faq->answer !!}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif

<form action="{{ route('bulten.store') }}" method="POST" data-subscribe-form class="site-subscribe">
    @csrf
    <input type="hidden" name="source" value="{{ $source }}">
    <div class="hp-field" aria-hidden="true">
        <label for="subscribe-website-{{ $source }}">Website</label>
        <input type="text" name="website" id="subscribe-website-{{ $source }}" tabindex="-1" autocomplete="off">
    </div>
    <div class="site-subscribe__alert" data-subscribe-alert hidden></div>
    <div class="site-subscribe__row">
        <input type="email" name="email" placeholder="E-posta adresiniz" required maxlength="150" autocomplete="email">
        <button class="theme-btn3" type="submit">Abone ol</button>
    </div>
    <span class="site-subscribe__error" data-error="email" hidden></span>
    <label class="site-subscribe__privacy">
        <input type="checkbox" name="privacy" value="1" required>
        <span>
            <a href="{{ route('kvkk') }}" target="_blank" rel="noopener noreferrer">Aydınlatma metnini</a>
            okudum, e-posta almak istiyorum.
        </span>
    </label>
    <span class="site-subscribe__error" data-error="privacy" hidden></span>
</form>

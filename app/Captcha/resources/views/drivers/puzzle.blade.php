{{--
    Bulmacaya özgü içerik. Panel çerçevesi (başlık, yenile/kapat düğmeleri,
    durum satırı) widget.blade.php'de duruyor — burada yalnızca yapboz kalır,
    başka bir sürücü eklenirse aynı çerçeveyi paylaşır.
--}}
<div class="cap-stage" data-captcha-stage>
    <img class="cap-bg" data-captcha-bg alt="" draggable="false">
    <img class="cap-piece" data-captcha-piece alt="" draggable="false">

    <div class="cap-veil" data-captcha-veil>
        <span class="cap-spinner" aria-hidden="true"></span>
        <span data-captcha-veil-text>Hazırlanıyor…</span>
    </div>

    <div class="cap-done" data-captcha-done>
        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="m4 12.5 5 5L20 6.5" /></svg>
        <span>Doğrulandı</span>
    </div>
</div>

<div class="cap-slider" data-captcha-slider>
    <span class="cap-fill" data-captcha-fill></span>
    <span class="cap-hint" data-captcha-hint>Parçayı yerine kaydırın</span>
    <span class="cap-handle" data-captcha-handle tabindex="0" role="slider"
        aria-label="Yapboz parçasını kaydırın" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
            <path d="m9 6 6 6-6 6" />
            <path d="m14 6 6 6-6 6" />
        </svg>
    </span>
</div>

/**
 * Google yorumları (.home-reviews) — slider kart yüksekliğini eşitler.
 */
(function () {
    if (typeof jQuery === 'undefined') {
        return;
    }

    function initSection(section) {
        const $slider = jQuery(section.querySelector('.testimonial14-slider-area'));
        if (!$slider.length) {
            return;
        }

        function equalizeReviewCards() {
            if (!$slider.hasClass('slick-initialized')) {
                return;
            }

            const $slides = $slider.find('.slick-slide');
            const $measureSlides = $slider.find('.slick-slide:not(.slick-cloned)');

            $slides.css('height', '');
            $slides.children().css('height', '');
            $slides.find('.testimonial14-boxarea').css('min-height', '');

            let maxHeight = 0;

            $measureSlides.each(function () {
                const height = jQuery(this).find('.testimonial14-boxarea').outerHeight();
                maxHeight = Math.max(maxHeight, height);
            });

            if (maxHeight <= 0) {
                return;
            }

            $slides.css('height', `${maxHeight}px`);
            $slides.children().css('height', '100%');
            $slides.find('.testimonial14-boxarea').css('min-height', `${maxHeight}px`);
        }

        function bind() {
            equalizeReviewCards();
            $slider.off('setPosition.homeReviews reInit.homeReviews afterChange.homeReviews');
            $slider.on('setPosition.homeReviews reInit.homeReviews afterChange.homeReviews', equalizeReviewCards);
        }

        function waitForSlick(attempts = 0) {
            if ($slider.hasClass('slick-initialized')) {
                bind();
                window.addEventListener('resize', equalizeReviewCards);
                return;
            }

            if (attempts < 40) {
                setTimeout(() => waitForSlick(attempts + 1), 50);
            }
        }

        waitForSlick();
    }

    function boot() {
        document.querySelectorAll('.home-reviews').forEach(initSection);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();

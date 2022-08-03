import $ from 'jquery';

const isSupportedObjectFit = () => 'object-fit' in document.body.style;

if (!isSupportedObjectFit()) {
    const applyPlyfill = $conteiter => {
        const $elements = $conteiter.find('[data-object-fit]');

        for (const el of $elements) {
            const $el = $(el);
            let className = 'object-fit-polyfill';

            if ($el.data('object-fit-class')) {
                className = $el.data('object-fit-class');
            }

            if ($el.hasClass(className)) {
                continue;
            }

            const $img = $el.find('img');

            $el
                .addClass(className)
                .css('background-image', `url(${$img.attr('src')})`);
            $img.addClass('hidden');
        }
    };

    applyPlyfill($('body'));

    $(document).on('initLayout content:changed', e => applyPlyfill($(e.target)));
}


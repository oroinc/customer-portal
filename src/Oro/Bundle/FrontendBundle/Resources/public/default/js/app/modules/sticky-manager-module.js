import $ from 'jquery';
import 'sticky-element';

const stickyName = 'sticky';

$(document)
    .on('initLayout content:changed', ({target}) => {
        $(target).find(`[data-${stickyName}]`).each((_, element) => {
            const data = $(element).data(stickyName) ?? {};
            $(element).stickyElement(typeof data === 'object' ? data : {});
        });
    })
    .on('disposeLayout content:remove', ({target}) => {
        $(target).find(`[data-${stickyName}]`).each((_, element) => {
            if ($(element).data(`oro.stickyElement`)) {
                $(element).stickyElement('dispose');
            }
        });
    });

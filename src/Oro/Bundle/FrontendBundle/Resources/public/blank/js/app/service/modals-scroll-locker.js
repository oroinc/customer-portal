import $ from 'jquery';
import _ from 'underscore';
import mediator from 'oroui/js/mediator';

const modals = {};

const scrollUpdate = () => {
    const $body = $('body');
    if (_.some(modals)) {
        $body.css({
            position: 'fixed',
            top: `-${window.scrollY}px`
        });
    } else {
        const scrollY = document.body.style.top;
        $body.css({
            position: '',
            top: ''
        });
        window.scrollTo(0, parseInt(scrollY || '0') * -1);
    }
};

export default () => {
    mediator.on({
        // widget dialogs
        'widget_dialog:open'(dialog) {
            if (dialog.widget.dialog('instance').options.modal) {
                modals[dialog.cid] = true;
                scrollUpdate();
            }
        },
        'widget_dialog:close'(dialog) {
            delete modals[dialog.cid];
            scrollUpdate();
        },
        // modals
        'modal:open'(modal) {
            modals[modal.cid] = true;
            scrollUpdate();
        },
        'modal:close'(modal) {
            delete modals[modal.cid];
            scrollUpdate();
        }
    });
};

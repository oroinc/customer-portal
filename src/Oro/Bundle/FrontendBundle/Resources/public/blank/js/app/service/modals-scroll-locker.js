import $ from 'jquery';
import Backbone from 'backbone';
import mediator from 'oroui/js/mediator';

const modals = {};
let isScrollLocked = false;

const scrollUpdate = () => {
    if (isScrollLocked === Object.keys(modals).length > 0) {
        return; // nothing to toggle
    }

    const $body = $('body');
    isScrollLocked = !isScrollLocked;
    if (isScrollLocked) {
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

const addModal = cid => {
    modals[cid] = true;
    scrollUpdate();
};

const removeModal = cid => {
    delete modals[cid];
    scrollUpdate();
};

export default () => {
    const observer = Object.create(Backbone.Events);

    mediator.on({
        // widget dialogs
        'widget_dialog:open'(dialog) {
            if (dialog.widget.dialog('instance').options.modal) {
                addModal(dialog.cid);
                observer.listenToOnce(dialog, 'dispose', removeModal.bind(void 0, dialog.cid));
            }
        },
        'widget_dialog:close'(dialog) {
            removeModal(dialog.cid);
            observer.stopListening(dialog);
        },
        // modals
        'modal:open': modal => addModal(modal.cid),
        'modal:close': modal => removeModal(modal.cid)
    });
};

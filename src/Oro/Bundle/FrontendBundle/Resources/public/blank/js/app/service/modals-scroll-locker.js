import Backbone from 'backbone';
import mediator from 'oroui/js/mediator';
import scrollLocker from 'oroui/js/app/services/body-scroll-locker';

export default () => {
    const observer = Object.create(Backbone.Events);

    mediator.on({
        // widget dialogs
        'widget_dialog:open'(dialog) {
            if (dialog.widget.dialog('instance').options.modal) {
                scrollLocker.addLocker(dialog.cid);
                observer.listenToOnce(dialog, 'dispose', () => scrollLocker.removeLocker(dialog.cid));
            }
        },
        'widget_dialog:close'(dialog) {
            scrollLocker.removeLocker(dialog.cid);
            observer.stopListening(dialog);
        },
        // modals
        'modal:open': modal => scrollLocker.addLocker(modal.cid),
        'modal:close': modal => scrollLocker.removeLocker(modal.cid)
    });
};

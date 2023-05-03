import Backbone from 'backbone';
import mediator from 'oroui/js/mediator';

const viewsWithOwnLoadingBars = {};
const toggleBodyClass = () => {
    document.body.classList.toggle('hide-app-loading-bar', Object.keys(viewsWithOwnLoadingBars).length);
};
export default () => {
    const observer = Object.create(Backbone.Events);

    mediator.on({
        // widget dialogs
        'widget_dialog:open'(dialog) {
            if (dialog.options.mobileLoadingBar || dialog.options.desktopLoadingBar) {
                viewsWithOwnLoadingBars[dialog.cid] = true;
                toggleBodyClass();
                observer.listenToOnce(dialog, 'dispose', () => {
                    delete viewsWithOwnLoadingBars[dialog.cid];
                    toggleBodyClass();
                });
            }
        },
        'widget_dialog:close'(dialog) {
            if (dialog.options.mobileLoadingBar || dialog.options.desktopLoadingBar) {
                observer.stopListening(dialog);
                delete viewsWithOwnLoadingBars[dialog.cid];
                toggleBodyClass();
            }
        }
    });
};

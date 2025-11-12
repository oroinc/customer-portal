import widgetManager from 'oroui/js/widget-manager';
import messenger from 'oroui/js/messenger';
import mediator from 'oroui/js/mediator';
import __ from 'orotranslation/js/translator';
import $ from 'jquery';


export default function(options) {
    if (options.savedId) {
        widgetManager.getWidgetInstance(
            options._wid,
            function(widget) {
                if (!options.message) {
                    options.message = __('oro_frontend.widget_form_component.save_flash_success');
                }

                messenger.notificationFlashMessage('success', options.message);
                mediator.trigger('widget_success:' + widget.getAlias(), options);
                mediator.trigger('widget_success:' + widget.getWid(), options);
                widget.trigger('formSave', options.savedId);
                widget.remove();
                widget.on('renderComplete', function() {
                    // workaround for conflict between jquery-ui and bootstrap
                    // that caused close button not to show on popup
                    $.fn.bootstrapBtn = $.fn.button.noConflict();
                });
            }
        );
    }
};

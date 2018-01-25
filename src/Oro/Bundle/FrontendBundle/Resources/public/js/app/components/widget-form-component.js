define(
    ['oroui/js/widget-manager', 'oroui/js/messenger', 'oroui/js/mediator', 'orotranslation/js/translator', 'jquery'],
    function(widgetManager, messenger, mediator, __, $) {
        'use strict';

        return function(options) {
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
    }
);

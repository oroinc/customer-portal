define(function(require) {
    'use strict';

    const routing = require('routing');
    const $ = require('jquery');
    const LoadingMask = require('oroui/js/app/views/loading-mask-view');
    require('jquery-ui/widget');

    $.widget('oroui.printPageWidget', {

        loadingMask: new LoadingMask({container: $('body')}),

        _create: function() {
            this._super();
            this.$el = this.element;
        },

        _init: function() {
            this._initEvents();
        },

        _initEvents: function() {
            this._on(this.$el, {
                click: this._windowPrint
            });
        },

        _windowPrint: function(event) {
            const $trigger = $(event.currentTarget);
            if ($trigger.attr('href')) {
                event.preventDefault();
            }

            const printFrame = document.createElement('iframe');
            printFrame.src = routing.generate('oro_order_frontend_print', {id: this.$el.attr('value')});
            printFrame.onload = function(self) {
                const closePrint = () => {
                    document.body.removeChild(this);
                    self._hideMask();
                };

                // If close the print window without printing.
                this.contentWindow.onbeforeunload = closePrint;
                // After printing the order page.
                this.contentWindow.onafterprint = closePrint;
            }.bind(printFrame, this);

            this._showMask();
            document.body.appendChild(printFrame);
        },

        _showMask: function() {
            this.loadingMask.show();
        },

        _hideMask: function() {
            this.loadingMask.hide();
        }
    });

    return 'printPageWidget';
});

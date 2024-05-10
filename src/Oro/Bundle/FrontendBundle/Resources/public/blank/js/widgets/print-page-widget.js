define(function(require) {
    'use strict';

    const $ = require('jquery');
    const LoadingMask = require('oroui/js/app/views/loading-mask-view');
    require('jquery-ui/widget');

    $.widget('oroui.printPageWidget', {
        options: {
            route: null
        },

        _create: function() {
            this._super();
            this.$el = this.element;
            this.loadingMask = new LoadingMask({container: $('body')});
        },

        _init: function() {
            this._initEvents();
        },

        _initEvents: function() {
            this._on(this.$el, {
                click: this._print
            });
        },

        _print: function(event) {
            const $trigger = $(event.currentTarget);

            if ($trigger.attr('href')) {
                event.preventDefault();
            }

            if (this.options.route) {
                this._iFramePrint(this.options.route);

                return;
            }

            // By default print current page.
            window.print();
        },

        _iFramePrint: function(route) {
            const printFrame = document.createElement('iframe');
            printFrame.src = route;
            printFrame.onload = () => {
                const closePrint = () => {
                    document.body.removeChild(printFrame);
                    this._hideMask();
                };

                // If close the print window without printing.
                printFrame.contentWindow.onbeforeunload = closePrint;
                // After printing the order page.
                printFrame.contentWindow.onafterprint = closePrint;
            };

            this._showMask();
            document.body.appendChild(printFrame);
        },

        _showMask: function() {
            this.loadingMask.show();
        },

        _hideMask: function() {
            this.loadingMask.hide();
        },

        _destroy: function() {
            if (this.loadingMask) {
                this.loadingMask.hide();
                this.loadingMask.dispose();
                delete this.loadingMask;
            }

            this._super();
        }
    });

    return 'printPageWidget';
});

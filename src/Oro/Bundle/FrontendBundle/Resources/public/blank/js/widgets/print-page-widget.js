define(function(require) {
    'use strict';

    const $ = require('jquery');
    require('jquery-ui/widget');

    $.widget('oroui.printPageWidget', {
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

            window.print();
        }
    });

    return 'printPageWidget';
});

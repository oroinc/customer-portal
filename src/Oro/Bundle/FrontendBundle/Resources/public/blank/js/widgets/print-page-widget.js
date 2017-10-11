define(function(require) {
    'use strict';

    var $ = require('jquery');
    require('jquery-ui');

    $.widget('oroui.printPageWidget', {
        _create: function() {
            this._super();
            this.$el = this.element;
        },

        _init: function() {
            this._initEvents();
        },

        _initEvents: function() {
            var $trigger = this.$el;

            this._on($trigger, {
                'click': this._windowPrint
            });
        },

        _windowPrint: function (event) {
            if (this.$el.is('[href]')) {
                event.preventDefault();
            }
            window.print();
        }
    });

    return 'printPageWidget';
});

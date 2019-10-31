define(function(require) {
    'use strict';

    const $ = require('jquery');
    const _ = require('underscore');
    const AbstractInputWidget = require('oroui/js/app/views/input-widget/abstract');

    const CheckboxInputWidget = AbstractInputWidget.extend({
        widgetFunction: function() {
            this.getContainer().on('keydown keypress', _.bind(this._handleEnterPress, this));
            this.$el.on('change', _.bind(this._handleChange, this));
        },

        _handleEnterPress: function(event) {
            if (event.which === 32) {
                event.preventDefault();
                this.$el.trigger('click');
            }
        },

        _handleChange: function() {
            const $content = $('[data-checkbox-triggered-content]');
            if (this.$el.prop('checked')) {
                this._on();
                $content.show();
            } else {
                this._off();
                $content.hide();
            }
        },

        _on: function() {
            this.$el.prop('checked', true);
            this.$el.parent().addClass('checked');
        },

        _off: function() {
            this.$el.prop('checked', false);
            this.$el.parent().removeClass('checked');
        },

        findContainer: function() {
            return this.$el.closest('label');
        }
    });

    return CheckboxInputWidget;
});

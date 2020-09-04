// Increases textarea height on caret return
define(function(require) {
    'use strict';

    const $ = require('jquery');
    require('jquery-ui/widget');

    $.widget('oroui.elasticAreaWidget', {
        _create: function() {
            this.$el = this.element;
            this._super();
        },

        _init: function() {
            this._disableScrollbar();
            this._initEvents();
        },

        _initEvents: function() {
            this._on(this.$el, {
                input: this.resize
            });
        },

        _destroy: function() {
            this.$el.removeAttr('style');
        },

        _disableScrollbar: function() {
            this.$el.css('overflow', 'hidden');
        },

        resize: function() {
            this.$el.height('auto');
            this.$el.height(this._getScrollHeight() - this._getDelta());
        },

        _getScrollHeight: function() {
            return this.$el[0].scrollHeight;
        },

        _getDelta: function() {
            return parseInt(this.$el.css('paddingBottom')) + parseInt(this.$el.css('paddingTop')) || 0;
        }
    });

    return 'elasticAreaWidget';
});

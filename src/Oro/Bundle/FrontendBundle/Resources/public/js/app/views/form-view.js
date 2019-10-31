define(function(require) {
    'use strict';

    const _ = require('underscore');
    const BaseView = require('oroui/js/app/views/base/view');
    const mediator = require('oroui/js/mediator');

    const FormView = BaseView.extend({
        options: {
            selectors: {}
        },

        fields: {},

        /**
         * @inheritDoc
         */
        constructor: function FormView(options) {
            FormView.__super__.constructor.call(this, options);
        },

        /**
         * @inheritDoc
         */lize: function(options) {
            this.options = _.defaults(options || {}, this.options);
            _.each(this.options.selectors, function(selector, key) {
                const $root = this.options._sourceElement || this.$el;
                this.fields[key] = $root.find(selector);
            }, this);

            this._setChangeListeners();
        },

        _setChangeListeners: function() {
            _.each(this.fields, function($field, key) {
                $field.on('change', function() {
                    mediator.trigger('update:' + key, $field.val());
                });
            });
        },

        dispose: function() {
            if (this.disposed) {
                return;
            }

            _.each(this.fields, function($field) {
                $field.off();
            });
        }
    });

    return FormView;
});

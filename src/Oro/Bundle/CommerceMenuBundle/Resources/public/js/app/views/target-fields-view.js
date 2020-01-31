define(function(require) {
    'use strict';

    var BaseView = require('oroui/js/app/views/base/view');
    var _ = require('underscore');

    var TargetFieldsView = BaseView.extend({
        options: {
            targetTypeField: null,
            contentNodeField: null,
            systemPageField: null,
            uriField: null
        },

        /**
         * {jQuery.Element}
         */
        $targetTypeField: null,

        /**
         * @inheritDoc
         */
        constructor: function TargetFieldsView(options) {
            TargetFieldsView.__super__.constructor.call(this, options);
        },

        /**
         * @inheritDoc
         */
        initialize: function(options) {
            this.options = _.extend({}, this.options, options);

            var requiredOptions = ['targetTypeField', 'contentNodeField', 'systemPageField', 'uriField'];
            _.each(requiredOptions, (function(optionName) {
                if (!this.options[optionName]) {
                    throw new Error(optionName + ' option is required');
                }
            }).bind(this));

            this.$targetTypeField = this.$('[name="' + this.options.targetTypeField + '"]');

            this.$targetTypeField.on('change', this._onTargetTypeChange.bind(this));

            this._onTargetTypeChange();

            TargetFieldsView.__super__.initialize.call(this, options);
        },

        _onTargetTypeChange: function() {
            switch (this.$targetTypeField.val()) {
                case 'content_node':
                    this._getField(this.options.contentNodeField).show();
                    this._getField(this.options.systemPageField).hide();
                    this._getField(this.options.uriField).hide();
                    break;
                case 'system_page':
                    this._getField(this.options.contentNodeField).hide();
                    this._getField(this.options.systemPageField).show();
                    this._getField(this.options.uriField).hide();
                    break;
                case 'uri':
                    this._getField(this.options.contentNodeField).hide();
                    this._getField(this.options.systemPageField).hide();
                    this._getField(this.options.uriField).show();
                    break;
            }
        },

        /**
         * @param {string} selector
         * @returns {jQuery.Element}
         * @private
         */
        _getField: function(selector) {
            return this.$(selector);
        },

        /**
         * @inheritDoc
         */
        dispose: function() {
            if (this.disposed) {
                return;
            }

            this.$targetTypeField.off('change', this._onTargetTypeChange.bind(this));

            return TargetFieldsView.__super__.dispose.call(this);
        }
    });

    return TargetFieldsView;
});

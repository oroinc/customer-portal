define(function(require) {
    'use strict';

    const BaseView = require('oroui/js/app/views/base/view');
    const _ = require('underscore');

    const TargetFieldsView = BaseView.extend({
        options: {
            targetTypeField: null,
            contentNodeField: null,
            categoryField: null,
            maxTraverseLevelField: null,
            systemPageField: null,
            uriField: null
        },

        /**
         * {jQuery.Element}
         */
        $targetTypeField: null,

        /**
         * @inheritdoc
         */
        constructor: function TargetFieldsView(options) {
            TargetFieldsView.__super__.constructor.call(this, options);
        },

        /**
         * @inheritdoc
         */
        initialize: function(options) {
            this.options = _.extend({}, this.options, options);

            const requiredOptions = [
                'targetTypeField',
                'contentNodeField',
                'categoryField',
                'maxTraverseLevelField',
                'systemPageField',
                'uriField'
            ];
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
                    this._getField(this.options.categoryField).hide();
                    this._getField(this.options.maxTraverseLevelField).show();
                    this._getField(this.options.systemPageField).hide();
                    this._getField(this.options.uriField).hide();
                    break;
                case 'category':
                    this._getField(this.options.contentNodeField).hide();
                    this._getField(this.options.categoryField).show();
                    this._getField(this.options.maxTraverseLevelField).show();
                    this._getField(this.options.systemPageField).hide();
                    this._getField(this.options.uriField).hide();
                    break;
                case 'system_page':
                    this._getField(this.options.contentNodeField).hide();
                    this._getField(this.options.categoryField).hide();
                    this._getField(this.options.maxTraverseLevelField).hide();
                    this._getField(this.options.systemPageField).show();
                    this._getField(this.options.uriField).hide();
                    break;
                case 'uri':
                    this._getField(this.options.contentNodeField).hide();
                    this._getField(this.options.categoryField).hide();
                    this._getField(this.options.maxTraverseLevelField).hide();
                    this._getField(this.options.systemPageField).hide();
                    this._getField(this.options.uriField).show();
                    break;
                case 'none':
                    this._getField(this.options.contentNodeField).hide();
                    this._getField(this.options.categoryField).hide();
                    this._getField(this.options.maxTraverseLevelField).hide();
                    this._getField(this.options.systemPageField).hide();
                    this._getField(this.options.uriField).hide();
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
         * @inheritdoc
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

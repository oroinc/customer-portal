define(function(require) {
    'use strict';

    const BaseView = require('oroui/js/app/views/base/view');
    const _ = require('underscore');

    const PageTemplateSelectView = BaseView.extend({
        /**
         * @property {String}
         */
        template: '<span class="page-template-description"><%- description %></span>',

        /**
         * @property {Object}
         */
        options: {
            descriptionContainer: '.description-container',
            selectSelector: 'select',
            metadata: {}
        },

        constructor: function PageTemplateSelectView(options) {
            PageTemplateSelectView.__super__.constructor.call(this, options);
        },

        /**
         * @inheritdoc
         */
        initialize: function(options) {
            this.options = _.extend({}, this.options, options);
            this.$selector = this.$el.find(this.options.selectSelector);
            this.$descriptionContainer = this.$el.find(this.options.descriptionContainer);

            this.delegate('change', this.options.selectSelector, this.render);

            this.render();
        },

        /**
         * @inheritdoc
         */
        getTemplateData: function() {
            const selectedPageTemplate = this.$selector.val();

            if (_.has(this.options.metadata, selectedPageTemplate)) {
                return {
                    description: this.options.metadata[selectedPageTemplate]
                };
            }

            return false;
        },

        /**
         * @inheritdoc
         */
        render: function() {
            const templateFunction = this.getTemplateFunction();
            const data = this.getTemplateData();

            if (data !== false) {
                this.$descriptionContainer.html(
                    templateFunction(data)
                );
                this.$descriptionContainer.show();
            } else {
                this.$descriptionContainer.hide();
            }
        }
    });

    return PageTemplateSelectView;
});

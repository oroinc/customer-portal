define(function(require) {
    'use strict';

    var PageTemplateSelectView;
    var BaseView = require('oroui/js/app/views/base/view');
    var _ = require('underscore');

    PageTemplateSelectView = BaseView.extend({
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

        constructor: function PageTemplateSelectView() {
            PageTemplateSelectView.__super__.constructor.apply(this, arguments);
        },

        /**
         * @inheritDoc
         */
        initialize: function(options) {
            this.options = _.extend({}, this.options, options);
            this.$selector = this.$el.find(this.options.selectSelector);
            this.$descriptionContainer = this.$el.find(this.options.descriptionContainer);

            this.delegate('change', this.options.selectSelector, this.render);

            this.render();
        },

        /**
         * @inheritDoc
         */
        getTemplateData: function() {
            var selectedPageTemplate = this.$selector.val();

            if (_.has(this.options.metadata, selectedPageTemplate)) {
                return {
                    description: this.options.metadata[selectedPageTemplate]
                };
            }

            return false;
        },

        /**
         * @inheritDoc
         */
        render: function() {
            var templateFunction = this.getTemplateFunction();
            var data = this.getTemplateData();

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

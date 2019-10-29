define(function(require) {
    'use strict';

    const BaseView = require('oroui/js/app/views/base/view');
    const _ = require('underscore');

    const ThemeSelectView = BaseView.extend({
        /**
         * @property {String}
         */
        template: '<span class="theme-description"><%= description %></span>',

        /**
         * @property {Object}
         */
        options: {
            descriptionContainer: '.description-container',
            selectSelector: 'select',
            metadata: {}
        },

        /**
         * @inheritDoc
         */
        constructor: function ThemeSelectView(options) {
            ThemeSelectView.__super__.constructor.call(this, options);
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
            const selectedTheme = this.$selector.val();

            if (_.has(this.options.metadata, selectedTheme)) {
                return this.options.metadata[selectedTheme];
            }

            return false;
        },

        /**
         * @inheritDoc
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

    return ThemeSelectView;
});

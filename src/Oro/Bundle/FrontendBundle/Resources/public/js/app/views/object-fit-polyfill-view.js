define(function(require) {
    'use strict';

    const BaseView = require('oroui/js/app/views/base/view');

    const ObjectFitPolyfillView = BaseView.extend({
        /**
         * @inheritDoc
         */
        constructor: function ObjectFitPolyfillView(options) {
            ObjectFitPolyfillView.__super__.constructor.call(this, options);
        },

        /**
         * @inheritDoc
         */
        initialize: function(options) {
            if (!this.isSupportObjectFit()) {
                this.setImageElement();
                this.render();
            }

            ObjectFitPolyfillView.__super__.initialize.call(this, options);
        },

        /**
         * Check if browser support object-fit
         */
        isSupportObjectFit: function() {
            return 'object-fit' in document.body.style;
        },

        /**
         * Set Image Element
         */
        setImageElement: function() {
            this.$image = this.$el.find('img');
        },

        /**
         * Get Image Element
         */
        getImageElement: function() {
            return this.$image;
        },

        /**
         * Get image URL
         */
        getImageUrl: function() {
            return this.getImageElement().prop('src');
        },

        /**
         * Hide image Element
         */
        hideImageElement: function() {
            this.getImageElement().addClass('hidden');
        },

        /**
         * @inheritDoc
         */
        render: function() {
            this.$el.css({
                'background-image': 'url(' + this.getImageUrl() + ')'
            });

            this.hideImageElement();

            return this;
        }
    });

    return ObjectFitPolyfillView;
});

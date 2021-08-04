define(function(require) {
    'use strict';

    const BaseComponent = require('oroui/js/app/components/base/component');
    const $ = require('jquery');
    const _ = require('underscore');

    const CustomerAddressComponent = BaseComponent.extend({
        /**
         * @property {Object}
         */
        targetElement: null,

        /**
         * @property {Object}
         */
        options: {
            defaultsSelector: '[name$="[defaults][default][]"]',
            typesSelector: '[name$="[types][]"]',
            containerSelector: '[data-content="address-form"]'
        },

        /**
         * @inheritdoc
         */
        constructor: function CustomerAddressComponent(options) {
            CustomerAddressComponent.__super__.constructor.call(this, options);
        },

        /**
         * @inheritdoc
         */
        initialize: function(options) {
            this.options = _.defaults(options || {}, this.options);
            this.targetElement = $(options._sourceElement);
            if (options.disableDefaultWithoutType) {
                this.disableDefaultWithoutType();
            }

            if (options.disableRepeatedTypes) {
                this.disableRepeatedTypes();
            }
        },

        /**
         * @inheritdoc
         */
        dispose: function() {
            if (this.disposed || !this.targetElement) {
                return;
            }

            this.targetElement.off('click', this.options.defaultsSelector);
            this.targetElement.off('click', this.options.typesSelector);

            CustomerAddressComponent.__super__.dispose.call(this);
        },

        disableDefaultWithoutType: function() {
            /**
             * Switch off default checkbox when type unselected
             */
            _.each(this.targetElement.find(this.options.defaultsSelector), this.processDefaultsChange, this);

            this.targetElement.on('click', this.options.defaultsSelector, function(event) {
                this.processDefaultsChange(event.target);
            }.bind(this));

            _.each(this.targetElement.find(this.options.typesSelector), this.processTypeChange, this);

            this.targetElement.on('click', this.options.typesSelector, function(event) {
                this.processTypeChange(event.target);
            }.bind(this));
        },

        /**
         * @param {Element} el
         */
        processDefaultsChange: function(el) {
            if (el.checked) {
                $(el).closest(this.options.containerSelector)
                    .find(this.options.typesSelector + '[value="' + el.value + '"]')
                    .prop('checked', true);
            }
        },

        /**
         * @param {Element} el
         */
        processTypeChange: function(el) {
            if (!el.checked) {
                $(el).closest(this.options.containerSelector)
                    .find(this.options.defaultsSelector + '[value="' + el.value + '"]')
                    .prop('checked', false);
            }
        },

        disableRepeatedTypes: function() {
            /**
             * Allow only 1 item with selected default type
             */
            this.targetElement.on('click', this.options.defaultsSelector, function(event) {
                if (event.target.checked) {
                    this.targetElement
                        .find(this.options.defaultsSelector + '[value="' + event.target.value + '"]')
                        .not(event.target)
                        .prop('checked', false);
                }
            }.bind(this));
        }
    });

    return CustomerAddressComponent;
});

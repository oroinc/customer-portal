define(function(require) {
    'use strict';

    var CustomerAddressComponent;
    var BaseComponent = require('oroui/js/app/components/base/component');
    var $ = require('jquery');
    var _ = require('underscore');

    CustomerAddressComponent = BaseComponent.extend({
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
            containerSelector: '*[data-content]'
        },

        /**
         * @inheritDoc
         */
        constructor: function CustomerAddressComponent() {
            CustomerAddressComponent.__super__.constructor.apply(this, arguments);
        },

        /**
         * @inheritDoc
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
         * @inheritDoc
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
            var self = this;

            this.targetElement.find(this.options.defaultsSelector).each(function(idx, defaultsEl) {
                self.processDefaultsChange(defaultsEl);
            });
            this.targetElement.on('click', this.options.defaultsSelector, function() {
                self.processDefaultsChange(this);
            });

            this.targetElement.find(this.options.typesSelector).each(function(idx, typeEl) {
                self.processTypeChange(typeEl);
            });
            this.targetElement.on('click', this.options.typesSelector, function() {
                self.processTypeChange(this);
            });
        },

        /**
         * @param {Element} el
         */
        processDefaultsChange: function(el) {
            if (el.checked) {
                var items = $(el).closest(this.options.containerSelector)
                    .find(this.options.typesSelector + '[value="' + el.value + '"]');

                items.each(function(idx, typeEl) {
                    typeEl.checked = true;
                });
            }
        },

        /**
         * @param {Element} el
         */
        processTypeChange: function(el) {
            var items = $(el).closest(this.options.containerSelector)
                .find(this.options.defaultsSelector + '[value="' + el.value + '"]');

            if (!el.checked) {
                items.each(function(idx, defaultsEl) {
                    defaultsEl.checked = false;
                });
            }
        },

        disableRepeatedTypes: function() {
            /**
             * Allow only 1 item with selected default type
             */
            var self = this;
            this.targetElement.on('click', this.options.defaultsSelector, function() {
                if (this.checked) {
                    var selector = self.options.defaultsSelector + '[value="' + this.value + '"]';
                    self.targetElement.find(selector).each(function(idx, el) {
                        el.checked = false;
                    });
                    this.checked = true;
                }
            });
        }
    });

    return CustomerAddressComponent;
});

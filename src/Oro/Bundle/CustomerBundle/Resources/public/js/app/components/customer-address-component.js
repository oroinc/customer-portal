define(function(require) {
    'use strict';

    var CustomerAddressComponent;
    var BaseComponent = require('oroui/js/app/components/base/component');
    var $ = require('jquery');

    CustomerAddressComponent = BaseComponent.extend({
        /**
         * @property {Object}
         */
        targetElement: null,

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
            this.targetElement = $(options._sourceElement);
            if (options.disableDefaultWithoutType) {
                this.disableDefaultWithoutType();
            }

            if (options.disableRepeatedTypes) {
                this.disableRepeatedTypes();
            }
        },

        dispose: function() {
            if (this.disposed || !this.targetElement) {
                return;
            }

            this.targetElement.off('click', '[name$="[defaults][default][]"]');
            this.targetElement.off('click', '[name$="[types][]"]');

            CustomerAddressComponent.__super__.dispose.call(this);
        },

        disableDefaultWithoutType: function() {
            /**
             * Switch off default checkbox when type unselected
             */
            var self = this;
            this.targetElement.on('click', '[name$="[defaults][default][]"]', function(event) {
                if (this.checked) {
                    var items = $(event.target.closest('*[data-content]'))
                        .find('[name$="[types][]"][value="' + this.value + '"]');

                    items.each(function(idx, el) {
                        el.checked = true;
                    });
                }
            });

            this.targetElement.on('click', '[name$="[types][]"]', function() {
                var defaultTypeName = this.name.replace('[types][]', '[defaults][default][]');
                var selector = '[name$="' + defaultTypeName + '"][value="' + this.value + '"]';
                var defaultCheckboxes = self.targetElement.find(selector);

                if (!this.checked) {
                    defaultCheckboxes.each(function(idx, el) {
                        el.checked = false;
                    });
                }
            });
        },

        disableRepeatedTypes: function() {
            /**
             * Allow only 1 item with selected default type
             */
            var self = this;
            this.targetElement.on('click', '[name$="[defaults][default][]"]', function() {
                if (this.checked) {
                    var selector = '[name$="[defaults][default][]"][value="' + this.value + '"]';
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

import BaseAddressValidatedAtView from 'oroaddressvalidation/js/app/views/base-address-validated-at-view';
import $ from 'jquery';

const CustomerAddressValidatedAtView = BaseAddressValidatedAtView.extend({
    /**
     * @inheritDoc
     */
    optionNames: BaseAddressValidatedAtView.prototype.optionNames.concat([
        'isShippingTypeValidationEnabled',
        'isBillingTypeValidationEnabled'
    ]),

    /**
     * @property {jQuery.Element|null}
     */
    $types: null,

    /**
     * @property {Boolean}
     */
    isShippingTypeValidationEnabled: false,

    /**
     * @property {Boolean}
     */
    isBillingTypeValidationEnabled: false,

    /**
     * @inheritDoc
     */
    constructor: function CustomerAddressValidatedAtView(options) {
        CustomerAddressValidatedAtView.__super__.constructor.call(this, options);
    },

    /**
     * @inheritDoc
     */
    initialize(options) {
        CustomerAddressValidatedAtView.__super__.initialize.call(this, options);

        this.$types = this.$addressForm.find('[data-name="field__types"]');
    },

    /**
     * @inheritDoc
     */
    onFormSubmit(event) {
        if (this.$validatedAt.val()) {
            return;
        }

        if (!this._isValidationEnabled()) {
            return;
        }

        CustomerAddressValidatedAtView.__super__.onFormSubmit.call(this, event);
    },

    /**
     * @inheritDoc
     */
    updateAddressForm($addressForm) {
        this.$addressForm
            .find(':input')
            .not('[data-name="field__id"]')
            .each((index, input) => {
                const $input = $(input);
                const $newInput = $addressForm.find(':input[name="' + $input.attr('name') + '"]');

                if (!$newInput.length ||
                    $newInput.closest('[data-name="field__types"]').length ||
                    $newInput.closest('[data-name="field__default"]').length) {
                    return;
                }

                $input.val($newInput.val());
                $input.inputWidget('refresh');
                $input.prop('disabled', $newInput.prop('disabled'));
            });
    },

    /**
     * Checks if an address must be validated as per enabled address types.
     *
     * @returns {Boolean}
     * @private
     */
    _isValidationEnabled() {
        if (!this.isShippingTypeValidationEnabled && !this.isBillingTypeValidationEnabled) {
            return false;
        }

        const isShipping = this.$types.find('[value="shipping"]:checked').length === 1;
        if (isShipping && this.isShippingTypeValidationEnabled) {
            return true;
        }

        const isBilling = this.$types.find('[value="billing"]:checked').length === 1;
        if (isBilling && this.isBillingTypeValidationEnabled) {
            return true;
        }

        return false;
    }
});

export default CustomerAddressValidatedAtView;

import __ from 'orotranslation/js/translator';
import $ from 'jquery';
import BaseView from 'oroui/js/app/views/base/view';
import FrontendAddressValidationDialogWidget
    from 'oroaddressvalidation/js/app/views/frontend-address-validation-dialog-widget';

const FrontendCustomerAddressValidatedAtView = BaseView.extend({
    /**
     * @inheritDoc
     */
    optionNames: BaseView.prototype.optionNames.concat([
        'dialogUrl',
        'isShippingTypeValidationEnabled',
        'isBillingTypeValidationEnabled'
    ]),

    /**
     * @property {string}
     */
    dialogUrl: '',

    /**
     * @property {Boolean}
     */
    isShippingTypeValidationEnabled: false,

    /**
     * @property {Boolean}
     */
    isBillingTypeValidationEnabled: false,

    /**
     * @property {jQuery.Element|null}
     */
    $types: null,

    /**
     * @property {jQuery.Element|null}
     */
    $form: null,

    /**
     * @property {jQuery.Element|null}
     */
    $addressForm: null,

    /**
     * @property {jQuery.Element|null}
     */
    $addressLabel: null,

    /**
     * @property {jQuery.Element|null}
     */
    $validatedAt: null,

    /**
     * @property {Boolean}
     */
    skipAddressValidation: false,

    /**
     * @inheritDoc
     */
    constructor: function FrontendCustomerAddressValidatedAtView(options) {
        FrontendCustomerAddressValidatedAtView.__super__.constructor.call(this, options);
    },

    /**
     * @inheritDoc
     */
    initialize(options) {
        this.$form = this.$el.closest('form');
        this.$addressForm = this.$el.closest('[data-content="address-form"]');
        this.$validatedAt = this.$el.find('[data-name="field__validated-at"]');
        this.$addressLabel = this.$addressForm.find('[data-name="field__label"]');
        this.$types = this.$addressForm.find('[data-name="field__types"]');

        FrontendCustomerAddressValidatedAtView.__super__.initialize.call(this, options);
    },

    /**
     * @inheritDoc
     */
    delegateListeners() {
        FrontendCustomerAddressValidatedAtView.__super__.delegateListeners.call(this);

        this.listenTo(this.$form, {submit: this.onFormSubmit.bind(this)});
        this.listenTo(this.$addressForm, {change: this.onAddressChange.bind(this)});
    },

    /**
     * Opens the Address Validation dialog, listens to its submit.
     *
     * @param {jQuery.Event} event
     */
    onFormSubmit(event) {
        if (this.$validatedAt.val()) {
            return;
        }

        if (!this.isValidationEnabled()) {
            return;
        }

        this.$form.validate();

        if (!this.$form.valid()) {
            return;
        }

        if (this.subview('dialog') || this.$validatedAt.val() || this.skipAddressValidation) {
            this.skipAddressValidation = false;

            return;
        }

        event.preventDefault();
        event.stopImmediatePropagation();

        this.openAddressValidationDialog();
    },

    openAddressValidationDialog() {
        this.subview('dialog', new FrontendAddressValidationDialogWidget({
            title: this.getDialogTitle(),
            url: this.dialogUrl,
            addressFormData: this.$addressForm.find(':input').serializeArray()
        }));

        this.listenToOnce(this.subview('dialog'), {
            success: this.onFormSubmitDialogSuccess.bind(this),
            fail: this.onFormSubmitDialogFail.bind(this),
            reset: this.onDialogReset.bind(this),
            close: this.onDialogClose.bind(this)
        });

        this.subview('dialog').render();
    },

    /**
     * @returns {String}
     */
    getDialogTitle() {
        const label = this.$addressLabel.val();

        if (label !== '') {
            return __('oro.address_validation.frontend.dialog.title_long', {label: label});
        } else {
            return __('oro.address_validation.frontend.dialog.title_short');
        }
    },

    /**
     * Updates the address form, closes the Address Validation dialog and submits the parent form.
     *
     * @param {Object} event
     */
    onFormSubmitDialogSuccess(event) {
        this.onDialogSuccess(event);

        this.$form.trigger('submit');
    },

    /**
     * Updates the address form and closes the Address Validation dialog.
     *
     * @param {Object} event
     */
    onDialogSuccess(event) {
        if (!event.addressForm) {
            return;
        }

        this.updateAddressForm($(event.addressForm));

        this.subview('dialog').remove();
    },

    /**
     * Updates the address form with the new form.
     *
     * @param {jQuery.Element} $addressForm
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
     * Closes the Address Validation dialog and submits the parent form.
     *
     * @param {Object} event
     */
    onFormSubmitDialogFail(event) {
        this.onDialogFail(event);

        this.skipAddressValidation = true;
        this.$form.trigger('submit');
    },

    /**
     * Closes the Address Validation dialog.
     *
     * @param {Object} event
     */
    onDialogFail(event) {
        if (event.reason === 'no_address') {
            // Not a case user should be notified - empty address form has been submitted,
            // so there is nothing to validate so far.
        } else {
            throw new Error('Unsupported failure reason: ' + event.reason || '<no reason>');
        }

        this.removeSubview('dialog');
    },

    /**
     * Called when user clicks on "Edit Address" button.
     */
    onDialogReset() {
    },

    /**
     * Resets the dialog property to null when it is closed.
     */
    onDialogClose() {
        this.removeSubview('dialog');
    },

    /**
     * Clears the validatedAt field when address fields are changed.
     */
    onAddressChange() {
        this.$validatedAt.val(null);
    },

    /**
     * Checks if an address must be validated as per enabled address types.
     *
     * @returns {Boolean}
     */
    isValidationEnabled() {
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

export default FrontendCustomerAddressValidatedAtView;

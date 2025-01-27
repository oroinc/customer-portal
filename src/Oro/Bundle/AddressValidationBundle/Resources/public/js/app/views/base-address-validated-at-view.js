import BaseView from 'oroui/js/app/views/base/view';
import AddressValidationDialogWidget from 'oroaddressvalidation/js/app/views/address-validation-dialog-widget';
import $ from 'jquery';
import __ from 'orotranslation/js/translator';

const BaseAddressValidatedAtView = BaseView.extend({
    /**
     * @inheritDoc
     */
    optionNames: BaseView.prototype.optionNames.concat([
        'dialogUrl'
    ]),

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
    constructor: function BaseAddressValidatedAtView(options) {
        BaseAddressValidatedAtView.__super__.constructor.call(this, options);
    },

    /**
     * @inheritDoc
     */
    initialize(options) {
        this.$form = this.$el.closest('form');
        this.$addressForm = this.$el.closest('[data-content="address-form"]');
        this.$validatedAt = this.$el.find('[data-name="field__validated-at"]');
        this.$addressLabel = this.$addressForm.find('[data-name="field__label"]');

        BaseAddressValidatedAtView.__super__.initialize.call(this, options);
    },

    /**
     * @inheritDoc
     */
    delegateListeners() {
        BaseAddressValidatedAtView.__super__.delegateListeners.call(this);

        this.listenTo(this.$form, {submit: this.onFormSubmit.bind(this)});
        this.listenTo(this.$addressForm, {change: this.onAddressChange.bind(this)});
    },

    createDialog() {
        return new AddressValidationDialogWidget({
            title: this.getDialogTitle(),
            url: this.dialogUrl,
            addressFormData: this.$addressForm.find(':input').serializeArray()
        });
    },

    /**
     * @returns {String}
     */
    getDialogTitle() {
        const label = this.$addressLabel.val();

        if (label !== '') {
            return __('oro.address_validation.dialog.title_long', {label: label});
        } else {
            return __('oro.address_validation.dialog.title_short');
        }
    },

    /**
     * Opens the Address Validation dialog, listens to its submit.
     *
     * @param {jQuery.Event} event
     */
    onFormSubmit(event) {
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

        this.subview('dialog', this.createDialog());

        this.listenToOnce(this.subview('dialog'), {
            success: this.onFormSubmitDialogSuccess.bind(this),
            fail: this.onFormSubmitDialogFail.bind(this),
            reset: this.onDialogReset.bind(this),
            close: this.onDialogClose.bind(this)
        });

        this.subview('dialog').render();
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
                if (!$newInput.length) {
                    return;
                }

                if ($input.data('inputWidget')) {
                    $input.inputWidget('val', $newInput.val());
                } else {
                    $input.val($newInput.val());
                }

                $input.prop('disabled', $newInput.prop('disabled'));
            });
    },

    /**
     * Clears the validatedAt field when address fields are changed.
     */
    onAddressChange() {
        this.$validatedAt.val(null);
    }
});

export default BaseAddressValidatedAtView;

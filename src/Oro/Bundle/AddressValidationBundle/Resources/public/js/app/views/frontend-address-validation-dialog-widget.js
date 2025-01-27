import DialogWidget from 'oro/dialog-widget';
import _ from 'underscore';
import $ from 'jquery';

const FrontendAddressValidationDialogWidget = DialogWidget.extend({
    /**
     * @inheritDoc
     */
    options: _.extend({}, DialogWidget.prototype.options, {
        method: 'POST',
        addressFormData: null,
        dialogOptions: {
            modal: true,
            resizable: true,
            autoResize: true,
            allowMaximize: false
        }
    }),

    /**
     * @property {Array}
     */
    addressFormData: null,

    /**
     * @inheritDoc
     */
    constructor: function FrontendAddressValidationDialogWidget(options) {
        FrontendAddressValidationDialogWidget.__super__.constructor.call(this, options);
    },

    /**
     * @inheritDoc
     */
    initialize(options) {
        options.dialogOptions = _.extend(
            {},
            this.options.dialogOptions,
            options.dialogOptions || {},
            {title: options.title}
        );
        this.addressFormData = options.addressFormData || [];

        FrontendAddressValidationDialogWidget.__super__.initialize.call(this, options);
    },

    /**
     * @inheritDoc
     */
    delegateListeners() {
        FrontendAddressValidationDialogWidget.__super__.delegateListeners.call(this);

        this.listenTo(this, {renderComplete: this._onRenderComplete.bind(this)});
    },

    /**
     * Adds listeners to select/update the suggested address radio button when select2 dropdown is changed.
     *
     * @private
     */
    _onRenderComplete() {
        const $suggestedSelect = this.$el.find('select');
        const $addressRadio = this.$el.find('[data-name="field__address"]');
        const $updateAddressCheckbox = this.$el.find('[data-name="field__update-address"]');
        const $updateAddressCheckboxParent = $updateAddressCheckbox.closest('.control-group-checkbox');

        $updateAddressCheckbox.prop('disabled', true);
        this.listenTo($suggestedSelect, {
            'select2-open': () => {
                $suggestedSelect.closest('.choice-widget-expanded__item').find('label').trigger('click');
            },
            'select2-close': () => {
                $('#' + $suggestedSelect.closest('.choice-widget-expanded__item').find('label').attr('for'))
                    .attr('value', $suggestedSelect.val());
            }
        });

        this.listenTo($addressRadio, {
            change: event => {
                const isSuggestedAddress = $(event.target).val() !== '0';
                $updateAddressCheckbox.prop('disabled', !isSuggestedAddress);
                $updateAddressCheckboxParent.toggleClass('hide', !isSuggestedAddress);

                if (!isSuggestedAddress) {
                    $updateAddressCheckbox.prop('checked', false);
                }
            }
        });
    },

    /**
     * @inheritDoc
     */
    prepareContentRequestOptions(data, method, url) {
        data = (data !== undefined ? data + '&' : '') + $.param(this.addressFormData);

        return FrontendAddressValidationDialogWidget
            .__super__
            .prepareContentRequestOptions
            .call(this, data, method, url);
    }
});

export default FrontendAddressValidationDialogWidget;

import _ from 'underscore';
import ApiAccessor from 'oroui/js/tools/api-accessor';
import mediator from 'oroui/js/mediator';
import viewportManager from 'oroui/js/viewport-manager';
import BaseView from 'oroui/js/app/views/base/view';
import template from
    'tpl-loader!orofrontend/templates/currency-localization/currency-localization-form.html';
import SwitcherFieldView from './switcher-field-view';

const CurrencyLocalizationFormView = BaseView.extend({
    optionNames: BaseView.prototype.optionNames.concat([
        'localizations',
        'currencies',
        'showCurrencySymbol',
        'localizationAndCurrencyRoute',
        'allowOptionsControl',
        'redirectRoute',
        'redirectRouteParameters',
        'redirectQueryParameters'
    ]),

    autoRender: true,

    template,

    localizationAndCurrencyRoute: 'oro_frontend_set_current_currency_and_localization',

    redirectRoute: 'oro_frontend_root',

    redirectRouteParameters: null,

    redirectQueryParameters: null,

    currencies: [],

    allowOptionsControl: false,

    events: {
        'submit': 'submitControl',
        'keyup': 'onKeyup',
        'click .toggle-container-vertical > label': 'changedRadio',
        'reset': 'resetForm'
    },

    constructor: function CurrencyLocalizationFormView(...args) {
        CurrencyLocalizationFormView.__super__.constructor.call(this, ...args);
    },

    submitControl(event) {
        event.preventDefault();
        CurrencyLocalizationFormView.sendRequest(this.prepareRequestPayload());
    },

    prepareRequestPayload() {
        const searchParams = new URLSearchParams(this.$el.serialize());
        const currency = searchParams.get('currency');
        const localization = searchParams.get('localization');

        return {
            currency: currency || this.getSelected(this.currencies).value,
            localization: localization || this.getSelected(this.localizations).value,
            redirectRoute: this.redirectRoute,
            redirectRouteParameters: this.redirectRouteParameters,
            redirectQueryParameters: this.redirectQueryParameters
        };
    },

    resetForm() {
        this.$('select').each((_index, item) => {
            if (item.name === 'currency') {
                this.$(item).val(this.getSelected(this.currencies).value).change();
            }

            if (item.name === 'localization') {
                this.$(item).val(this.getSelected(this.localizations).value).change();
            }
        });
    },

    getSelected(items) {
        return items.find(item => item.selected);
    },

    changedRadio(event) {
        setTimeout(() => this.submitControl(event), 0);
    },

    getTemplateFunction(key) {
        return CurrencyLocalizationFormView.__super__.getTemplateFunction.call(this, key);
    },

    isMobileBigScreen() {
        return viewportManager.isApplicable('mobile-big');
    },

    onKeyup(event) {
        if (event.keyCode === 13) {
            this.submitControl(event);
        }
    },

    getData() {
        return [{
            id: this.cid,
            title: _.__('oro_frontend.dropdown.currency_localization_form.localization_title'),
            name: 'localization',
            switcherMaxCount: 2,
            isSwitcherVertical: this.allowOptionsControl && this.isMobileBigScreen() && this.currencies.length <= 1,
            items: this.localizations.map(item => ({
                value: item.id,
                label: item.title,
                selected: item.selected
            }))
        }, {
            id: this.cid,
            title: _.__('oro_frontend.dropdown.currency_localization_form.currency_title'),
            name: 'currency',
            switcherMaxCount: 4,
            isSwitcherVertical: this.allowOptionsControl && this.isMobileBigScreen() && this.localizations.length <= 1,
            items: this.currencies.map(item => ({
                value: item.code,
                label: item.title,
                selected: item.selected
            }))
        }];
    },

    render() {
        CurrencyLocalizationFormView.__super__.render.call(this);

        this.getData().forEach(data => {
            if (data.items.length < 2) {
                return;
            }

            this.subview(data.name, new SwitcherFieldView({
                container: this.$el.find('.currency-localization-control__content'),
                noWrap: true,
                isSwitcherVertical: data.isSwitcherVertical,
                name: data.name,
                title: data.title,
                switcherMaxCount: data.switcherMaxCount,
                items: data.items
            }));
        });

        return this;
    },

    getTemplateData() {
        return {
            id: this.cid,
            widthSubmitButton: !this.isMobileBigScreen() || this.currencies.length > 1 && this.localizations.length > 1
        };
    }
}, {
    sendRequest(params = {}) {
        mediator.execute('showLoading');

        const apiAccessor = new ApiAccessor({
            route: CurrencyLocalizationFormView.prototype.localizationAndCurrencyRoute,
            http_method: 'POST'
        });

        apiAccessor.send({}, params)
            .then(({currencySuccessful, localizationSuccessful, redirectTo}) => {
                if (!currencySuccessful) {
                    mediator.execute('showFlashMessage', 'error', 'Selected currency is not enabled.');
                }

                if (!localizationSuccessful) {
                    mediator.execute('showFlashMessage', 'error', 'Selected localization is not enabled.');
                }

                if (redirectTo) {
                    mediator.execute('redirectTo', {url: redirectTo}, {redirect: true}).fail(() => {
                        mediator.execute('hideLoading');
                    });
                } else {
                    mediator.execute('hideLoading');
                }
            }).fail(() => {
                mediator.execute('hideLoading');
            });
    }
});

export default CurrencyLocalizationFormView;

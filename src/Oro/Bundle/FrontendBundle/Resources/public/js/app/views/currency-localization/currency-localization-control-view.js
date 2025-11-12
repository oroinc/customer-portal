import BaseView from 'oroui/js/app/views/base/view';
import viewportManager from 'oroui/js/viewport-manager';
import CurrencyLocalizationForm from './currency-localization-form';

import template from 'tpl-loader!orofrontend/templates/currency-localization/currency-localization-view.html';
import templateDropdown
    from 'tpl-loader!orofrontend/templates/currency-localization/currency-localization-dropdown-view.html';

import condensedDoubleViewTpl
    from 'tpl-loader!orofrontend/templates/currency-localization/condensed-double-view.html';
import condensedMultipleViewTpl
    from 'tpl-loader!orofrontend/templates/currency-localization/condensed-multiple-view.html';
import condensedSingleViewTpl
    from 'tpl-loader!orofrontend/templates/currency-localization/condensed-single-view.html';

const CurrencyLocalizationControlView = BaseView.extend({
    optionNames: BaseView.prototype.optionNames.concat([
        'localizations',
        'currencies',
        'showCurrencySymbol',
        'sidePanelMode',
        'redirectRoute',
        'redirectRouteParameters',
        'redirectQueryParameters',
        'triggerClass'
    ]),

    template,

    templateDropdown,

    condensedMultipleViewTpl,

    condensedSingleViewTpl,

    condensedDoubleViewTpl,

    localizationSwitcherRoute: 'oro_frontend_localization_frontend_set_current_localization',

    redirectRoute: 'oro_frontend_root',

    redirectRouteParameters: null,

    redirectQueryParameters: null,

    sidePanelMode: true,

    events: {
        'click .dropdown-menu': 'onDropdownToggle',
        'click [data-localization], [data-currency]': 'handleSwitching',
        'change [name="currency"], [name="localization"]': 'onChange',
        'reset form': 'resetLabels'
    },

    listen: {
        'layout:content-relocated mediator': 'onRelocated',
        'viewport:mobile-big mediator': 'render',
        'main-fullscreen-side-panel:footer:shown mediator': 'render',
        'main-fullscreen-side-panel:footer:closed mediator': 'disposeForm'
    },

    constructor: function CurrencyLocalizationControlView(...args) {
        this.localizations = {};
        this.currencies = {};
        CurrencyLocalizationControlView.__super__.constructor.call(this, ...args);
    },

    onChange({target}) {
        switch (target.name) {
            case 'localization':
                this.updateLabels('localization', this.getLocalizations().items.find(
                    ({value}) => value === parseInt(target.value)
                ).title);
                break;
            case 'currency':
                this.updateLabels('currency',
                    this.getCurrencies().items.find(({value}) => value === target.value).title);
                break;
            default:
        }
    },

    resetLabels() {
        this.updateLabels('localization', this.getLocalizations().selected.title);
        this.updateLabels('currency', this.getCurrencies().selected.title);

        if (this.$el.find('[data-toggle="dropdown"]').length) {
            this.$el.find('[data-toggle="dropdown"]').dropdown('hide');
        }
    },

    onDropdownToggle(e) {
        if (this.$(e.target).attr('type') !== 'button' && this.$(e.target).attr('type') !== 'submit') {
            e.stopPropagation();
        }
    },

    onRelocated(element, options) {
        if (this.$el.is(element)) {
            this.sidePanelMode = options !== void 0;

            this.render();
        }
    },

    isMobileBig() {
        return viewportManager.isApplicable('mobile-big');
    },

    getLocalizations() {
        return {
            name: 'localization',
            selected: this.localizations.find(item => item.selected),
            icon: 'globe',
            items: this.localizations,
            length: this.localizations.length
        };
    },

    getCurrencies() {
        return {
            name: 'currency',
            selected: this.currencies.find(item => item.selected),
            icon: 'credit-card',
            items: this.currencies,
            length: this.currencies.length
        };
    },

    handleSwitching(event) {
        const localization = this.$(event.target).data('localization') || this.getLocalizations().selected.id;
        const currency = this.$(event.target).data('currency') || this.getCurrencies().selected.code;

        CurrencyLocalizationForm.sendRequest({
            currency,
            localization,
            redirectRoute: this.redirectRoute,
            redirectRouteParameters: this.redirectRouteParameters,
            redirectQueryParameters: this.redirectQueryParameters
        });
    },

    getTemplateData() {
        const localizations = this.getLocalizations();
        const currencies = this.getCurrencies();

        const availableControls = [];

        if (localizations.length) {
            availableControls.push('localizations');
        }

        if (currencies.length) {
            availableControls.push('currencies');
        }

        return {
            localizations,
            currencies,
            availableControls,
            iconChevron: this.isMobileBig() ? 'chevron-up' : 'chevron-right',
            icon: currencies.length > 1 ? 'credit-card' : 'globe',
            name: currencies.length === 2 ? 'currencies' : 'localizations',
            triggerClass: this.triggerClass
        };
    },

    getTemplateFunction(templateKey = 'templateDropdown') {
        if (this.isMobileBig()) {
            templateKey = 'template';
        }

        if (this.sidePanelMode) {
            templateKey = 'condensedMultipleViewTpl';

            const {items: localizations} = this.getLocalizations();
            const {items: currencies} = this.getCurrencies();

            if (
                currencies.length === 0 && localizations.length ||
                localizations.length === 0 && currencies.length ||
                currencies.length === 1 && localizations.length > 2 ||
                currencies.length > 2 && localizations.length === 1
            ) {
                templateKey = 'condensedSingleViewTpl';
            } else if (
                currencies.length === 1 && localizations.length === 2 ||
                currencies.length === 2 && localizations.length === 1
            ) {
                templateKey = 'condensedDoubleViewTpl';
            }
        }

        return CurrencyLocalizationControlView.__super__.getTemplateFunction.call(this, templateKey);
    },

    render() {
        CurrencyLocalizationControlView.__super__.render.call(this);

        if (this.$('[data-localization-form-container]').length) {
            this.subview('currencyLocalizationForm', new CurrencyLocalizationForm({
                container: this.$('[data-localization-form-container]'),
                currencies: this.currencies,
                localizations: this.localizations,
                showCurrencySymbol: this.showCurrencySymbol,
                redirectRoute: this.redirectRoute,
                redirectRouteParameters: this.redirectRouteParameters,
                redirectQueryParameters: this.redirectQueryParameters,
                allowOptionsControl: this.sidePanelMode,
                noWrap: true
            }));
        }

        return this;
    },

    disposeForm() {
        if (this.subview('currencyLocalizationForm')) {
            this.subview('currencyLocalizationForm').dispose();
        }
    },

    updateLabels(name, label) {
        if (this.$(`[data-name="${name}-label"]`).length && label) {
            this.$(`[data-name="${name}-label"]`).text(label);
        }
    }
});

export default CurrencyLocalizationControlView;

import BaseView from 'oroui/js/app/views/base/view';
import template from 'tpl-loader!orofrontend/templates/currency-localization/switcher-field/select-field.html';
import switcherTpl from 'tpl-loader!orofrontend/templates/currency-localization/switcher-field/switcher-field.html';
import switcherVerticalTpl
    from 'tpl-loader!orofrontend/templates/currency-localization/switcher-field/switcher-vertical-field.html';

const SwitcherFieldView = BaseView.extend({
    optionNames: BaseView.prototype.optionNames.concat([
        'switcherMaxCount',
        'isSwitcherVertical',
        'items',
        'title',
        'name'
    ]),

    autoRender: true,

    switcherMaxCount: 2,

    isSwitcherVertical: false,

    template,
    switcherTpl,
    switcherVerticalTpl,

    items: [],

    constructor: function SwitcherFieldView(...args) {
        SwitcherFieldView.__super__.constructor.apply(this, args);
    },

    getTemplateFunction(key) {
        if (this.isSwitcherVertical) {
            key = 'switcherVerticalTpl';
        } else if (this.items.length <= this.switcherMaxCount) {
            key = 'switcherTpl';
        }

        return SwitcherFieldView.__super__.getTemplateFunction.call(this, key);
    },

    getTemplateData() {
        return {
            id: this.cid,
            items: this.items,
            name: this.name,
            title: this.title
        };
    }
});

export default SwitcherFieldView;

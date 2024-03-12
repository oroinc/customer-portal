import BaseView from 'oroui/js/app/views/base/view';
import template from 'tpl-loader!orofrontend/default/templates/button-input.html';

const IncrementButtonView = BaseView.extend({
    optionNames: BaseView.prototype.optionNames.concat([
        'icon', 'extraClass', 'dataType', 'ariaLabel', 'disabled'
    ]),

    template,

    disabled: false,

    constructor: function IncrementButtonView(options) {
        IncrementButtonView.__super__.constructor.call(this, options);
    },

    getTemplateData() {
        return {
            extraClass: this.extraClass,
            dataType: this.dataType,
            icon: this.icon,
            disabled: this.disabled,
            ariaLabel: this.ariaLabel
        };
    }
});

export default IncrementButtonView;

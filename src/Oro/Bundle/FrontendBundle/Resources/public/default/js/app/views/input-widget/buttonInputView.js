import BaseView from 'oroui/js/app/views/base/view';
import template from 'tpl-loader!orofrontend/default/templates/button-input.html';

const ButtonInputView = BaseView.extend({
    optionNames: BaseView.prototype.optionNames.concat([
        'icon', 'extraClass', 'dataType', 'onClick', 'disabled'
    ]),

    autoRender: true,

    template,

    events: {
        click: 'clickHandler'
    },

    disabled: false,

    constructor: function ButtonInputView(options) {
        ButtonInputView.__super__.constructor.call(this, options);
    },

    clickHandler() {
        if (typeof this.onClick === 'function') {
            this.onClick();
        }
    },

    getTemplateData() {
        return {
            extraClass: this.extraClass,
            dataType: this.dataType,
            icon: this.icon,
            disabled: this.disabled
        };
    }
});

export default ButtonInputView;

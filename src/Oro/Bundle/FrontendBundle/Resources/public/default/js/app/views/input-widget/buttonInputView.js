import _ from 'underscore';
import BaseView from 'oroui/js/app/views/base/view';

const template = _.template(`
    <% let oroui = _.macros('oroui') %>
    <button type="button" class="btn btn--plain btn--icon input-quantity-btn <%- extraClass %>" data-type="<%- dataType %>">
        <%= oroui.renderIcon({name: icon}) %>
    </button>
`);

const ButtonInputView = BaseView.extend({
    optionNames: BaseView.prototype.optionNames.concat([
        'icon', 'extraClass', 'dataType',
    ]),

    autoRender: true,

    template,

    getTemplateData() {
        return {
            extraClass: this.extraClass,
            dataType: this.dataType,
            icon: this.icon
        };
    }
});

export default ButtonInputView;

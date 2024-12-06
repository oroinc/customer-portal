import BaseView from 'oroui/js/app/views/base/view';
import template from 'tpl-loader!orofrontend/default/templates/datagrid/grid-view-inline-rename-action.html';

const FrontendGridViewsInlineRenameView = BaseView.extend({
    template,

    events: {
        'submit form': 'onSubmit',
        'reset form': 'onCancel'
    },

    constructor: function FrontendGridViewsInlineRenameView(...args) {
        FrontendGridViewsInlineRenameView.__super__.constructor.apply(this, args);
    },

    render() {
        FrontendGridViewsInlineRenameView.__super__.render.call(this);

        const $input = this.$('[name="label"]');
        $input.trigger('focus');
        $input.get(0).setSelectionRange($input.val().length, $input.val().length);

        return this;
    },

    onSubmit(event) {
        event.preventDefault();

        this.model.set('label', this.$('[name="label"]').val(), {silent: true});

        this.trigger('save');
    },

    onCancel() {
        this.trigger('cancel');
    }
});

export default FrontendGridViewsInlineRenameView;

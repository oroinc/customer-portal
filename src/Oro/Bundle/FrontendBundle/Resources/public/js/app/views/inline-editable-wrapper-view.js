import BaseView from 'oroui/js/app/views/base/view';
import template from 'tpl-loader!../../../templates/editor/inline-editable-wrapper-view.html';

/**
 * @class
 */
const InlineEditorWrapperView = BaseView.extend({
    template,

    events: {
        'click [data-role="start-editing"]': 'onInlineEditingStart'
    },

    /**
     * @inheritdoc
     */
    constructor: function InlineEditorWrapperView(options) {
        InlineEditorWrapperView.__super__.constructor.call(this, options);
    },

    onInlineEditingStart: function() {
        this.trigger('start-editing');
    },

    getContainer: function() {
        return this.$('[data-role="container"]');
    }
});

export default InlineEditorWrapperView;

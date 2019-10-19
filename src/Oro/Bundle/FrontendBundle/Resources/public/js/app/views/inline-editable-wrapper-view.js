define(function(require) {
    'use strict';
    var BaseView = require('oroui/js/app/views/base/view');

    /**
     * @class
     */
    var InlineEditorWrapperView = BaseView.extend({
        template: require('tpl-loader!../../../templates/editor/inline-editable-wrapper-view.html'),

        events: {
            'click [data-role="start-editing"]': 'onInlineEditingStart'
        },

        /**
         * @inheritDoc
         */
        constructor: function InlineEditorWrapperView() {
            InlineEditorWrapperView.__super__.constructor.apply(this, arguments);
        },

        onInlineEditingStart: function() {
            this.trigger('start-editing');
        },

        getContainer: function() {
            return this.$('[data-role="container"]');
        }
    });

    return InlineEditorWrapperView;
});

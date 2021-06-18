define(function(require) {
    'use strict';
    const BaseView = require('oroui/js/app/views/base/view');

    /**
     * @class
     */
    const InlineEditorWrapperView = BaseView.extend({
        template: require('tpl-loader!../../../templates/editor/inline-editable-wrapper-view.html'),

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

    return InlineEditorWrapperView;
});

define(function(require) {
    'use strict';

    /**
     * Title cell content editor.
     *
     * @augments TextEditorView
     * @exports TitleEditorView
     */
    const TextEditorView = require('oroform/js/app/views/editor/text-editor-view');

    const TitleEditorView = TextEditorView.extend(/** @lends TitleEditorView.prototype */{
        template: require('tpl-loader!../../../../templates/editor/title-editor.html'),

        className: 'inline-view-editor',

        /**
         * @inheritdoc
         */
        constructor: function TitleEditorView(options) {
            TitleEditorView.__super__.constructor.call(this, options);
        }
    });

    return TitleEditorView;
});

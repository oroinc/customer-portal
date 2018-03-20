define(function(require) {
    'use strict';

    /**
     * Title cell content editor.
     *
     * @augments TextEditorView
     * @exports TitleEditorView
     */
    var TitleEditorView;
    var TextEditorView = require('oroform/js/app/views/editor/text-editor-view');

    TitleEditorView = TextEditorView.extend(/** @lends TitleEditorView.prototype */{
        template: require('tpl!../../../../templates/editor/title-editor.html'),

        className: 'inline-view-editor',

        /**
         * @inheritDoc
         */
        constructor: function TitleEditorView() {
            TitleEditorView.__super__.constructor.apply(this, arguments);
        }
    });

    return TitleEditorView;
});

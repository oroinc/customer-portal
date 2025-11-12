/**
 * Title cell content editor.
 *
 * @augments TextEditorView
 * @exports TitleEditorView
 */
import TextEditorView from 'oroform/js/app/views/editor/text-editor-view';
import template from 'tpl-loader!../../../../templates/editor/title-editor.html';

const TitleEditorView = TextEditorView.extend(/** @lends TitleEditorView.prototype */{
    template,

    className: 'inline-view-editor',

    /**
     * @inheritdoc
     */
    constructor: function TitleEditorView(options) {
        TitleEditorView.__super__.constructor.call(this, options);
    }
});

export default TitleEditorView;

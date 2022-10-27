define(function(require) {
    'use strict';

    const TextEditorView = require('oroform/js/app/views/editor/text-editor-view');

    /**
     * Multiline text cell content editor.
     *
     * ### Column configuration samples:
     * ``` yml
     * datagrids:
     *   {grid-uid}:
     *     inline_editing:
     *       enable: true
     *     # <grid configuration> goes here
     *     columns:
     *       {column-name-2}:
     *         inline_editing:
     *           editor:
     *             view: oroform/js/app/views/editor/multiline-text-editor-view
     *             view_options:
     *               placeholder: '<placeholder>'
     *               css_class_name: '<class-name>'
     *           validation_rules:
     *             NotBlank: ~
     * ```
     *
     * ### Options in yml:
     *
     * Column option name                                  | Description
     * :---------------------------------------------------|:-----------
     * inline_editing.editor.view_options.placeholder      | Optional. Placeholder translation key for an empty element
     * inline_editing.editor.view_options.placeholder_raw  | Optional. Raw placeholder value
     * inline_editing.editor.view_options.css_class_name   | Optional. Additional css class name for editor view DOM el
     * inline_editing.editor.validation_rules | Optional. Validation rules. See [documentation](../../../../FormBundle/Resources/doc/reference/js_validation.md#conformity-server-side-validations-to-client-once)
     *
     * ### Constructor parameters
     *
     * @class
     * @param {Object} options - Options container
     * @param {Object} options.model - Current row model
     * @param {string} options.fieldName - Field name to edit in model
     * @param {string} options.placeholder - Placeholder translation key for an empty element
     * @param {string} options.placeholder_raw - Raw placeholder value. It overrides placeholder translation key
     * @param {Object} options.validationRules - Validation rules. See [documentation here](../../../../FormBundle/Resources/doc/reference/js_validation.md#conformity-server-side-validations-to-client-once)
     *
     * @augments [TextEditorView](./text-editor-view.md)
     * @exports MultilineTextEditorView
     */
    const MultilineTextEditorView = TextEditorView.extend(/** @lends MultilineTextEditorView.prototype */{
        /**
         * @inheritdoc
         */
        constructor: function MultilineTextEditorView(options) {
            MultilineTextEditorView.__super__.constructor.call(this, options);
        },

        /**
         * Generic keydown handler, which handles ENTER
         *
         * @param {$.Event} e
         */
        onGenericEnterKeydown: function(e) {
            if (e.ctrlKey && e.keyCode === this.ENTER_KEY_CODE) {
                this.trigger('saveAndExitAction');
                e.stopImmediatePropagation();
                e.preventDefault();
            }
        }
    });

    return MultilineTextEditorView;
});

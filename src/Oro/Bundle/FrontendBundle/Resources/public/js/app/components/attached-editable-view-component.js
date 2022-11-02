define(function(require) {
    'use strict';

    const InlineEditableViewComponent = require('oroform/js/app/components/inline-editable-view-component');
    const BaseModel = require('oroui/js/app/models/base/model');
    const BaseView = require('oroui/js/app/views/base/view');
    const $ = require('jquery');
    const loadModules = require('oroui/js/app/services/load-modules');
    const frontendTypeMap = require('oroform/js/tools/frontend-type-map');
    const _ = require('underscore');

    const AttachedEditableViewComponent = InlineEditableViewComponent.extend(/** @lends AttachedEditableViewComponent.prototype */{
        /**
         * @inheritdoc
         */
        constructor: function AttachedEditableViewComponent(options) {
            AttachedEditableViewComponent.__super__.constructor.call(this, options);
        },

        /**
         * @constructor
         * @param {Object} options
         */
        initialize: function(options) {
            options = $.extend(true, {}, this.options, options);

            this.messages = options.messages;
            this.metadata = options.metadata;
            this.fieldName = options.fieldName;
            this.inlineEditingOptions = options.metadata.inline_editing;
            // frontend type mapped to viewer/editor/reader
            const frontendType = options.hasOwnProperty('frontend_type') ? options.frontend_type : 'text';
            this.classes = frontendTypeMap[frontendType];

            this.model = new BaseModel();
            this.model.set(this.fieldName, options.value);

            const waitors = [];
            waitors.push(loadModules.fromObjectProp(this.inlineEditingOptions.save_api_accessor, 'class').then(
                () => {
                    const ConcreteApiAccessor = this.inlineEditingOptions.save_api_accessor['class'];
                    this.saveApiAccessor = new ConcreteApiAccessor(
                        _.omit(this.inlineEditingOptions.save_api_accessor, 'class'));
                }
            ));

            this.deferredInit = $.when(...waitors);

            this.$el = options._sourceElement;
            let wrapperElement = this.$el.find('[data-role="editor"]');
            if (!wrapperElement.length) {
                wrapperElement = this.$el;
            }
            this.wrapper = new BaseView({el: wrapperElement});

            this.startEditing();
        },

        startEditing: function() {
            this.enterEditMode();
        },

        isInsertEditorModeOverlay: function() {
            return false;
        },

        enterEditMode: function() {
            if (!this.editorView) {
                const viewInstance = this.createEditorViewInstance();
                this.initializeEditorListeners(viewInstance);
            }

            return this.editorView;
        },

        exitEditMode: function() {
        },

        createEditorViewInstance: function() {
            const BaseEditor = this.classes.editor;
            const View = BaseEditor.extend({
                render: function() {
                    this.validator = this.$el.validate({
                        submitHandler: (form, e) => {
                            if (e && e.preventDefault) {
                                e.preventDefault();
                            }
                            this.trigger('saveAction');
                        },
                        errorPlacement: (error, element) => {
                            error.appendTo(this.$el);
                        },
                        rules: {
                            value: this.getValidationRules()
                        }
                    });
                    if (this.options.value) {
                        this.setFormState(this.options.value);
                    }
                    this.onChange();
                },

                getValue: function() {
                    return this.$el.val();
                },

                onFocusout: function(e) {
                    if (this.isChanged() && this.validator.form()) {
                        this.trigger('saveAction');
                    }

                    View.__super__.onFocusout.call(this, e);
                }
            });

            this.editorView = new View(this.getEditorOptions());

            return this.editorView;
        },

        getEditorOptions: function() {
            let element = this.wrapper.$(':input:first');
            if (!element.length) {
                element = this.wrapper.$el;
            }
            element.prop('disabled', false);

            return {
                el: element,
                autoRender: true,
                model: this.model,
                fieldName: this.fieldName
            };
        }
    });

    return AttachedEditableViewComponent;
});

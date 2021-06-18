define(function(require) {
    'use strict';

    const _ = require('underscore');
    const BaseView = require('oroui/js/app/views/base/view');

    const FakeMaskedInput = BaseView.extend({
        /**
         * @inheritdoc
         */
        events: {
            keypress: 'enterOnlyNumbers',
            input: 'toClear',
            mouseup: 'removeSelection',
            cut: 'removeSelection',
            copy: 'removeSelection'
        },

        /**
         * @inheritdoc
         */
        className: 'fake-masked-input',

        toChangeType: true,

        type: 'tel',

        /**
         * @inheritdoc
         * @returns {*}
         */
        constructor: function FakeMaskedInput(options) {
            return FakeMaskedInput.__super__.constructor.call(this, options);
        },

        /**
         * @inheritdoc
         */
        initialize: function(options) {
            _.extend(this, _.pick(options, ['toChangeType', 'type']));

            this.$el.addClass(this.className);

            if (this.toChangeType) {
                this._originalType = this.$el.attr('type');
                this.$el.attr('type', this.type);
            }

            this.toClear();
            FakeMaskedInput.__super__.initialize.call(this, options);
        },

        /**
         * Allow enter only numbers 0 - 9
         *
         * @param {jQuery.Event} event
         */
        enterOnlyNumbers: function(event) {
            const charCode = (event.which) ? event.which : event.keyCode;

            if (charCode > 31 && (charCode < 48 || charCode > 57)) {
                event.preventDefault();
            }
        },

        /**
         * Clear input field if present symbols other than 0 - 9
         */
        toClear: function() {
            if (this.$el.val().search(/\D/) !== -1) {
                this.$el.val('');
            }
        },

        /**
         * Forbidden selection if it browser support
         */
        removeSelection: function() {
            let selection = null;

            if ('getSelection' in window) {
                selection = window.getSelection();
            } else {
                // Browser does not support selection feature
                return;
            }

            const selectedText = this.el.value.substring(this.el.selectionStart, this.el.selectionEnd);

            // Nothing selected
            if (!selectedText.length) {
                return;
            }

            if (selection.empty) {
                selection.empty();
            } else if (selection.removeAllRanges) {
                selection.removeAllRanges();
            }

            this.el.selectionStart = null;
            this.el.selectionEnd = null;
        },

        /**
         * @inheritdoc
         */
        dispose: function() {
            if (this.disposed) {
                return;
            }

            this.$el.removeClass(this.className);
            if (this.toChangeType) {
                this.$el.attr('type', this._originalType);
            }
            FakeMaskedInput.__super__.dispose.call(this);
        }
    });

    return FakeMaskedInput;
});

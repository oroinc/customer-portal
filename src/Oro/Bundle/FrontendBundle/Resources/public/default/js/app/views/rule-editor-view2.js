define(function(require) {
    'use strict';

    var RuleEditorView;
    var BaseView = require('oroui/js/app/views/base/view');
    var $ = require('jquery');
    var _ = require('underscore');
    var Typeahead = $.fn.typeahead.Constructor;

    RuleEditorView = BaseView.extend({
        optionNames: BaseView.prototype.optionNames.concat([
            'component'
        ]),

        events: {
            'keyup': 'validate',
            'change': 'validate',
            'blur': 'validate',
            'paste': 'validate'
        },

        component: null,
        typeahead: null,
        autocompleteData: {},

        initialize: function(options) {
            this.initAutocomplete();
            return RuleEditorView.__super__.initialize.apply(this, arguments);
        },

        validate: function() {
            var isValid = this.component.isValid(this.$el.val());
            this.$el.toggleClass('error', !isValid);
            this.$el.parent().toggleClass('validation-error', !isValid);
        },

        initAutocomplete: function() {
            this.$el.typeahead({
                minLength: 0,
                items: 20,
                source: _.bind(this._typeaheadSource, this),
                lookup: _.bind(this._typeaheadLookup, this),
                highlighter: _.bind(this._typeaheadHighlighter, this),
                updater: _.bind(this._typeaheadUpdater, this)
            });

            var typeahead = this.typeahead = this.$el.data('typeahead');

            this.$el.on('focus click change', _.debounce(function() {
                typeahead.lookup();
            }));
        },

        _typeaheadSource: function() {
            var value = this.el.value;
            var position = this.el.selectionStart;

            this.autocompleteData = this.component.getAutocompleteData(value, position);
            this.typeahead.query = this.autocompleteData.query;
            return _.keys(this.autocompleteData.items);
        },

        _typeaheadLookup: function() {
            return this.typeahead.process(this.typeahead.source());
        },

        _typeaheadHighlighter: function(item) {
            var highlighter = Typeahead.prototype.highlighter;
            var hasChilds = !!this.autocompleteData.items[item].child;
            var suffix = hasChilds ? '&hellip;' : '';
            return highlighter.call(this.typeahead, item) + suffix;
        },

        _typeaheadUpdater: function(item) {
            this.component.updateValue(this.autocompleteData, item);
            var position = this.autocompleteData.position;
            this.$el.one('change', function() {
                this.selectionStart = this.selectionEnd = position;
            });

            return this.autocompleteData.value;
        },

        setUpdatedValue: function(query, item, position, newCaretPosition) {
            var $el = this.view.$el;
            var update = this._getUpdatedData(query, item, position);

            this._setCaretPosition($el, newCaretPosition || update.position || $el[0].selectionStart);

            return update.value;
        }
    });

    return RuleEditorView;
});

define(function(require) {
    'use strict';

    var RuleEditorView;
    var BaseView = require('oroui/js/app/views/base/view');
    require('bootstrap');
    var $ = require('jquery');
    var _ = require('underscore');
    var Typeahead = $.fn.typeahead.Constructor;

    RuleEditorView = BaseView.extend({
        optionNames: BaseView.prototype.optionNames.concat([
            'component', 'dataSource'
        ]),

        events: {
            'keyup': 'validate',
            'change': 'validate',
            'blur': 'validate',
            'paste': 'validate'
        },

        component: null,
        typeahead: null,

        autocompleteData: null,

        dataSource: null,

        dataSourceInstances: null,

        initialize: function(options) {
            this.autocompleteData = this.autocompleteData || {};
            this.dataSource = this.dataSource || {};
            this.dataSourceInstances = this.dataSourceInstances || {};
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
                select: _.bind(this._typeaheadSelect, this),
                source: _.bind(this._typeaheadSource, this),
                lookup: _.bind(this._typeaheadLookup, this),
                highlighter: _.bind(this._typeaheadHighlighter, this),
                updater: _.bind(this._typeaheadUpdater, this)
            });

            var typeahead = this.typeahead = this.$el.data('typeahead');

            this.$el.on('focus click input', _.debounce(function() {
                typeahead.lookup();
            }));
        },

        _typeaheadSource: function() {
            var value = this.el.value;
            var position = this.el.selectionStart;

            this.autocompleteData = this.component.getAutocompleteData(value, position);
            this._toggleDataSource();
            this.typeahead.query = this.autocompleteData.queryLast;
            return _.keys(this.autocompleteData.items);
        },

        _typeaheadLookup: function() {
            return this.typeahead.process(this.typeahead.source());
        },

        _typeaheadSelect: function() {
            var select = Typeahead.prototype.select;
            var result = select.apply(this.typeahead, arguments);
            this.typeahead.lookup();
            return result;
        },

        _typeaheadHighlighter: function(item) {
            var highlighter = Typeahead.prototype.highlighter;
            var hasChilds = !!this.autocompleteData.items[item].child;
            var suffix = hasChilds ? '&hellip;' : '';
            return highlighter.call(this.typeahead, item) + suffix;
        },

        _typeaheadUpdater: function(item) {
            this.component.updateQuery(this.autocompleteData, item);
            var position = this.autocompleteData.position;
            this.$el.one('change', function() {
                this.selectionStart = this.selectionEnd = position;
            });

            return this.autocompleteData.value;
        },

        getDataSource: function(dataSourceKey) {
            var dataSource = this.dataSourceInstances[dataSourceKey];
            if (!dataSource) {
                return this._initializeDataSource(dataSourceKey);
            }

            return dataSource;
        },

        _initializeDataSource: function(dataSourceKey) {
            var dataSource = this.dataSourceInstances[dataSourceKey] = {};

            dataSource.$widget = $(this.dataSource[dataSourceKey]);
            dataSource.$field = dataSource.$widget.find(':input[name]:first');
            dataSource.active = false;

            this._hideDataSource(dataSource);

            this.$el.after(dataSource.$widget).trigger('content:changed');

            dataSource.$field.on('change', _.bind(function() {
                if (!dataSource.active) {
                    return;
                }

                this.component.updateDataSourceValue(this.autocompleteData, dataSource.$field.val());
                this.$el.val(this.autocompleteData.value).change();
            }, this));

            return dataSource;
        },

        _toggleDataSource: function() {
            this._hideDataSources();

            var dataSourceKey = this.autocompleteData.dataSourceKey;
            var dataSourceValue = this.autocompleteData.dataSourceValue;

            if (_.isEmpty(dataSourceKey) || !_.has(this.dataSource, dataSourceKey)) {
                return;
            }

            this.autocompleteData.items = {};

            var dataSource = this.getDataSource(dataSourceKey);

            dataSource.$field.val(dataSourceValue).change();

            this._showDataSource(dataSource);
        },

        /**
         * Remove data source element
         *
         * @private
         */
        _hideDataSources: function() {
            _.each(this.dataSourceInstances, this._hideDataSource, this);
        },

        _hideDataSource: function(dataSource) {
            dataSource.active = false;
            dataSource.$widget.hide();
        },

        _showDataSource: function(dataSource) {
            dataSource.$widget.show();
            dataSource.active = true;
        }
    });

    return RuleEditorView;
});

define(function(require) {
    'use strict';

    var RuleEditorComponent;
    var ViewComponent = require('oroui/js/app/components/view-component');
    var _ = require('underscore');

    RuleEditorComponent = ViewComponent.extend({
        view: 'orofrontend/default/js/app/views/rule-editor-view',

        regex: {
            queryLeft: /^.*[ ]/g,
            queryRight: /[ ].*$/g
        },

        strings: {
            childSeparator: '.',
            itemSeparator: ' '
        },

        options: {
            operations: {
                math: ['+', '-', '%', '*', '/'],
                bool: ['AND', 'OR'],
                equality: ['==', '!='],
                compare: ['>', '<', '<=', '>='],
                inclusion: ['in', 'not in'],
                like: ['matches']
            },
            allowedOperations: ['math', 'bool', 'equality', 'compare', 'inclusion', 'like'],
            termLevelLimit: 3,
            entities: {
                root_entities: {},
                fields_data: {}
            }
        },

        allItems: null,

        operationsItems: null,

        entitiesItems: null,

        initialize: function(options) {
            this.options = _.defaults(options || {}, this.options);
            this.options.view = this.options.view || this.view;
            this.options.component = this;

            this._prepareAutocomplete();

            return RuleEditorComponent.__super__.initialize.apply(this, arguments);
        },

        isValid: function() {
            return true;
        },

        getAutocompleteData: function(value, position) {
            var autocompleteData = this._prepareAutocompleteData(value, position);

            this._setAutocompleteItems(autocompleteData);

            return autocompleteData;
        },

        updateValue: function(autocompleteData, item) {
            var hasChilds = !!autocompleteData.items[item].child;
            item += hasChilds ? this.strings.childSeparator : this.strings.itemSeparator;

            var queryParts = autocompleteData.queryParts.slice();
            queryParts.pop();
            queryParts.push(item);

            autocompleteData.value = autocompleteData.beforeQuery + queryParts.join(this.strings.childSeparator);
            autocompleteData.position = autocompleteData.value.length;
            autocompleteData.value += autocompleteData.afterQuery;
        },

        _prepareAutocomplete: function() {
            this.allItems = {};
            this.operationsItems = {};
            this.entitiesItems = {};

            this._prepareEntitiesItems();
            this._prepareOperationsItems();
        },

        _addItem: function(group, items, item, itemInfo) {
            if (itemInfo.child !== undefined && _.isEmpty(itemInfo.child)) {
                return;
            }

            itemInfo.group = group;
            if (itemInfo.parentItem) {
                itemInfo.item = itemInfo.parentItem + this.strings.childSeparator + item;
            } else {
                itemInfo.item = item;
            }

            this.allItems[itemInfo.item] = itemInfo;
            items[item] = itemInfo;
        },

        _prepareOperationsItems: function() {
            _.each(this.options.allowedOperations, function(type) {
                _.each(this.options.operations[type], function(item) {
                    this._addItem('operations', this.operationsItems, item, {
                        type: type
                    });
                }, this);
            }, this);
        },

        _prepareEntitiesItems: function() {
            _.each(this.options.entities.root_entities, function(item, entity) {
                this._addItem('entities', this.entitiesItems, item, {
                    child: this._getEntityChild(1, item, entity)
                });
            }, this);
        },

        _getEntityChild: function(level, parentItem, entity) {
            var childs = {};

            level++;
            if (level > this.options.termLevelLimit) {
                return childs;
            }

            _.each(this.options.entities.fields_data[entity], function(itemInfo, item) {
                var childItem = parentItem + this.strings.childSeparator + item;
                itemInfo.parentItem = parentItem;
                if (itemInfo.type === 'relation') {
                    itemInfo.child = this._getEntityChild(level, childItem, itemInfo.relation_alias);
                }
                this._addItem('entities', childs, item, itemInfo);
            }, this);

            return childs;
        },

        _prepareAutocompleteData: function(value, position) {
            var autocompleteData = {
                items: {},
                value: value,
                position: position,
                beforeQuery: null,
                afterQuery: null,
                queryFull: null,
                queryParts: null,
                query: null,
                group: null
            };

            this._setAutocompleteQuery(autocompleteData);
            this._setAutocompleteGroup(autocompleteData);

            return autocompleteData;
        },

        _setAutocompleteQuery: function(autocompleteData) {
            var beforeCaret = autocompleteData.value.slice(0, autocompleteData.position);
            var afterCaret = autocompleteData.value.slice(autocompleteData.position);

            var queryLeft = beforeCaret.replace(this.regex.queryLeft, '');
            var queryRight = afterCaret.replace(this.regex.queryRight, '');

            autocompleteData.beforeQuery = beforeCaret.slice(0, beforeCaret.length - queryLeft.length);
            autocompleteData.afterQuery = afterCaret.slice(queryRight.length);

            autocompleteData.queryFull = queryLeft + queryRight.split(this.strings.childSeparator).pop();
            autocompleteData.queryParts = autocompleteData.queryFull.split(this.strings.childSeparator);
            autocompleteData.query = autocompleteData.queryParts[autocompleteData.queryParts.length - 1];
        },

        _setAutocompleteGroup: function(autocompleteData) {
            var prevItem = _.trim(autocompleteData.beforeQuery).split(this.strings.itemSeparator).pop();
            prevItem = this.allItems[prevItem] || {};
            autocompleteData.group = prevItem.group === 'entities' ? 'operations' : 'entities';
        },

        _setAutocompleteItems: function(autocompleteData) {
            var queryParts = autocompleteData.queryParts;

            var items = this[autocompleteData.group + 'Items'];
            for (var i = 0; i < queryParts.length - 1; i++) {
                items = items[queryParts[i]] || {};
                items = items.child || null;
                if (!items) {
                    break;
                }
            }

            autocompleteData.items = items || {};
        }
    });

    return RuleEditorComponent;
});

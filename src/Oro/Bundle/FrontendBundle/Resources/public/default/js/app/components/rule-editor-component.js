define(function(require) {
    'use strict';

    var RuleEditorComponent;
    var ViewComponent = require('oroui/js/app/components/view-component');
    var _ = require('underscore');

    RuleEditorComponent = ViewComponent.extend({
        view: 'orofrontend/default/js/app/views/rule-editor-view',

        regex: {
            queryLeft: /^.*[ ]/g,
            queryRight: /[ ].*$/g,
            openBracket: /[\(]/g,
            closeBracket: /[\)]/g,
            array: /\[[^\[\]]*\]/,
            arrayF: /\[(.*?)\]/g,
            itemSeparator: /([ \(\)])/,
            dataSourceSeparator: '([ \\(\\)]*)',
            split: /([ \.])+/g
        },

        strings: {
            childSeparator: '.',
            itemSeparator: ' '
        },

        options: {
            operations: {
                math: ['+', '-', '%', '*', '/'],
                bool: ['and', 'or'],
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
            },
            dataSource: []
        },

        toNative: {
            'and': '&&',
            'or': '||',
            'boolean': 'true',
            'relation': 'true',
            'integer': '0',
            'float': '0',
            'standalone': '""',
            'string': '""',
            'in': '$next.indexOf($prev) != -1',
            'not in': '$next.indexOf($prev) == -1',
            'matches': '$prev.indexOf($next) != -1'
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

        /**
         * Check expression syntax
         *
         * @param {String} value
         * @returns {Boolean}
         */
        isValid: function(value) {
            value = _.trim(value);
            if (value.length === 0) {
                return true;
            }

            value = this._convertToNativeJS(value);
            if (value === false) {
                return false;
            }
            return this._checkNativeJS(value);
        },

        /**
         * Convert expression to native JS code
         *
         * @private
         * @param {String} value
         * @returns {Boolean|String}
         */
        _convertToNativeJS: function(value) {
            var clearMethods = ['_clearStrings', '_clearDataSource', '_clearArrays', '_clearSeparators'];
            for (var method in clearMethods) {
                value = this[clearMethods[method]](value);
                if (value === false) {
                    return false;
                }
            }

            var items = value.split(this.regex.itemSeparator);
            _.each(items, this._convertItemToNativeJS, this);

            return items.join('');
        },

        /**
         * Convert each item, or group of items, into native JS code
         *
         * @private
         * @param {String} item
         * @param {Integer} i
         * @param {Array} items
         */
        _convertItemToNativeJS: function(item, i, items) {
            if (item.length === 0) {
                return;
            }
            var prevSeparator = i - 1;
            var prevItem = prevSeparator - 1;
            var nextSeparator = i + 1;
            var nextItem = nextSeparator + 1;

            if (items[nextItem] && this.allItems[item + ' ' + items[nextItem]]) {
                //items with whitespaces, for example: not in
                item = item + ' ' + items[nextItem];
                items[nextItem] = '';
                items[nextSeparator] = '';

                nextSeparator = nextItem + 1;
                nextItem = nextSeparator + 1;
            }

            var nativeJS = this._getNativeJS(item);
            if (nativeJS === item) {
                return item;
            }

            if (nativeJS.indexOf('$prev') !== -1 && items[prevItem]) {
                nativeJS = nativeJS.replace('$prev', items[prevItem]);
                items[prevItem] = '';
                items[prevSeparator] = '';
            }

            if (nativeJS.indexOf('$next') !== -1 && items[nextItem]) {
                nativeJS = nativeJS.replace('$next', items[nextItem]);
                items[nextItem] = '';
                items[nextSeparator] = '';
            }

            items[i] = nativeJS;
        },

        /**
         * Make all strings empty, convert all strings to use double quote
         *
         * @private
         * @param {String} value
         * @returns {String}
         */
        _clearStrings: function(value) {
            value = value.replace(/\\\\/g, '');//remove "\" symbols
            value = value.replace(/\\['"]/g, '').replace(/\\/g, '');//remove \" and \'
            value = value.replace(/"[^"]*"/g, '""').replace(/'[^']*'/g, '""');//clear strings and convert quotes
            return value;
        },

        _clearDataSource: function(value) {
            var item;
            for (item in this.options.dataSource) {
                if (value.match(new RegExp(this.regex.dataSourceSeparator + item + '\\.'))) {
                    return false;
                }

                value = value.replace(new RegExp('(' + this.regex.dataSourceSeparator + item + ')' + '\\[[\\s]*\\d+\\s*\\]', 'g'), '$1');
            }

            return value;
        },

        /**
         * Make all arrays empty, remove nested arrays
         *
         * @private
         * @param {String} value
         * @returns {String|Boolean}
         */
        _clearArrays: function(value) {
            var array;
            var changedValue;

            //while we have an array
            //""array"" placeholder used to remove nested arrays
            while (value.indexOf('[') !== -1 && value.indexOf(']') !== -1) {
                array = value.match(this.regex.array);
                if (array.length === 0) {
                    //we have only one of [ or ], array not finished
                    return false;
                }

                if (!this._checkNativeJS(array[0].replace(/""array""/g, '[]'))) {
                    //array not valid
                    return false;
                }

                changedValue = value.replace(array[0], '""array""');
                if (changedValue === value) {
                    return false;//recursion
                }

                value = changedValue;
            }

            value = value.replace(/""array""/g, '[]');

            return value;
        },

        /**
         * Remove duplicated/extra whitespaces
         * @private
         * @param {String} value
         * @returns {String|Boolean}
         */
        _clearSeparators: function(value) {
            value = value.replace(/\s+/g, ' ');//remove duplicated whitespaces
            value = value.replace('( ', '(').replace(' )', ')');//remove before and after whitespaces brackets
            return value;
        },

        /**
         * Try to find native JS code for expression item
         *
         * @private
         * @param {String} value
         * @returns {String|Boolean}
         */
        _getNativeJS: function(value) {
            var item = this.allItems[value];
            if (this.toNative[value] !== undefined) {
                return this.toNative[value];
            } else if (item && this.toNative[item.type] !== undefined) {
                return this.toNative[item.type];
            }
            return value;
        },

        /**
         * Check native JS expression syntax
         *
         * @private
         * @param {String} value
         * @returns {Boolean}
         */
        _checkNativeJS: function(value) {
            //replace all "&&" and "||" to "&", because if first part of "&&" or "||" return true or false - JS ignore(do not execute) second part
            // and replace all ";" - we should accept only one expression(var test = 1; test == 1) will be failed
            value = value.replace(/&&|\|\|/g, '&').replace(/;/g, '');
            try {
                var f = new Function('return ' + value);
                var result = f();
                return _.isBoolean(result) || !_.isUndefined(result);
            } catch (e) {
                return false;
            }
        },

        getAutocompleteData: function(value, position) {
            var autocompleteData = this._prepareAutocompleteData(value, position);

            this._setAutocompleteItems(autocompleteData);
            this._setDataSource(autocompleteData);
            return autocompleteData;
        },

        updateValue: function(autocompleteData) {
            autocompleteData.value = autocompleteData.beforeQuery +
                autocompleteData.queryParts.join(this.strings.childSeparator) +
                autocompleteData.afterQuery;
        },

        updateQuery: function(autocompleteData, item) {
            var positionModificator = 0;
            var hasChild = !!autocompleteData.items[item].child;
            var hasDataSource = _.has(this.options.dataSource, item);

            if (hasDataSource) {
                item += '[]';
                positionModificator = hasChild ? -2 : -1;
            }

            item += hasChild ? this.strings.childSeparator : this.strings.itemSeparator;

            autocompleteData.queryParts[autocompleteData.queryIndex] = item;

            this.updateValue(autocompleteData);

            autocompleteData.position = autocompleteData.value.length - autocompleteData.afterQuery.length +
                positionModificator;
        },

        updateDataSourceValue: function(autocompleteData, dataSourceValue) {
            autocompleteData.queryParts[autocompleteData.queryIndex] = autocompleteData
                .query.replace(this.regex.arrayF, '[' + dataSourceValue + ']');

            this.updateValue(autocompleteData);
        },

        /**
         * @param {Object} autocompleteData
         * @private
         */
        _setDataSource: function(autocompleteData) {
            var dataSourceKey = autocompleteData.query.replace(this.regex.arrayF, '');
            var dataSourceValue = '';

            if (dataSourceKey === autocompleteData.query) {
                dataSourceKey = '';
            } else {
                dataSourceValue = this.regex.arrayF.exec(autocompleteData.query);
                dataSourceValue = dataSourceValue ? dataSourceValue[1] : '';
            }

            autocompleteData.dataSourceKey = dataSourceKey;
            autocompleteData.dataSourceValue = dataSourceValue;
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
            var child = {};

            level++;
            if (level > this.options.termLevelLimit) {
                return child;
            }

            _.each(this.options.entities.fields_data[entity], function(itemInfo, item) {
                var childItem = parentItem + this.strings.childSeparator + item;
                itemInfo.parentItem = parentItem;
                if (itemInfo.type === 'relation') {
                    itemInfo.child = this._getEntityChild(level, childItem, itemInfo.relation_alias);
                }
                this._addItem('entities', child, item, itemInfo);
            }, this);

            return child;
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

            autocompleteData.queryFull = queryLeft + queryRight;
            autocompleteData.queryParts = autocompleteData.queryFull.split(this.strings.childSeparator);
            autocompleteData.queryLast = autocompleteData.queryParts[autocompleteData.queryParts.length - 1];
            autocompleteData.queryIndex = queryLeft.split(this.strings.childSeparator).length - 1;
            autocompleteData.query = queryLeft.split(this.strings.childSeparator).pop() +
                queryRight.split(this.strings.childSeparator).shift();
        },

        _setAutocompleteGroup: function(autocompleteData) {
            var prevItemStr = _.trim(autocompleteData.beforeQuery).split(this.strings.itemSeparator).pop();
            var prevItem = this.allItems[prevItemStr] || {};

            if (!prevItemStr || prevItemStr === '(' || prevItem.group === 'operations') {
                autocompleteData.group = 'entities';
            } else {
                autocompleteData.group = 'operations';
            }
        },

        _setAutocompleteItems: function(autocompleteData) {
            var items = this[autocompleteData.group + 'Items'];
            var item;
            for (var i = 0; i < autocompleteData.queryParts.length - 1; i++) {
                item = autocompleteData.queryParts[i];
                item = item.replace(this.regex.arrayF, '');
                items = items[item] || {};
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

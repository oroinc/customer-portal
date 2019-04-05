define(function(require) {
    'use strict';

    var FrontendGrid;
    var Grid = require('orodatagrid/js/datagrid/grid');
    var __ = require('orotranslation/js/translator');

    FrontendGrid = Grid.extend({
        /**
         * Frontend currently grid options
         *
         * @property {Object}
         */
        gridOptions: null,

        /** @property {Object} */
        noDataTranslations: {
            entityHint: 'oro.datagrid.entityHint',
            noColumns: 'oro.datagrid.no.columns',
            noEntities: 'oro.datagrid.no.entities',
            noResults: 'oro_frontend.datagrid.no.results'
        },

        /**
         * @constructor
         */
        constructor: function FrontendGrid() {
            FrontendGrid.__super__.constructor.apply(this, arguments);
        },

        /**
         * @initialze
         */
        initialize: function() {
            FrontendGrid.__super__.initialize.apply(this, arguments);

            if (this.body) {
                this.collection.on('reset', function() {
                    this.body.render();
                }, this);
            }
        },

        /**
         * Update row class names for frontend grid
         *
         * @param {Object} options
         * @private
         */
        _initColumns: function(options) {
            this.gridOptions = options;
            this.updateRowClassNames();

            FrontendGrid.__super__._initColumns.apply(this, arguments);
        },

        /**
         * Update and concat row class names
         */
        updateRowClassNames: function() {
            if (Object.keys(this.rowActions).length > 0) {
                if (this.gridOptions.rowClassName) {
                    this.gridOptions.rowClassName = this.gridOptions.rowClassName + ' ' + this.rowActionsClass;
                } else {
                    this.gridOptions.rowClassName = this.rowClassName + ' ' + this.rowActionsClass;
                }
            }

            if (this.gridOptions.multiSelectRowEnabled) {
                if (this.gridOptions.rowClassName) {
                    this.gridOptions.rowClassName = this.gridOptions.rowClassName + ' ' + this.rowSelectClass;
                } else {
                    this.gridOptions.rowClassName = this.rowClassName + ' ' + this.rowSelectClass;
                }
            }
        },

        /**
         * @inheritDoc
         */
        getEmptyGridMessage: function(placeholders) {
            var translation = this.noColumnsFlag
                ? this.noDataTranslations.noColumns : this.noDataTranslations.noEntities;

            return this.noDataTemplate({
                hints: __(translation, placeholders).replace('\n', '<br />')
            });
        },

        /**
         * @inheritDoc
         */
        getEmptyGridCustomMessage: function(message) {
            return this.noDataTemplate({
                hints: message
            });
        },

        /**
         * @inheritDoc
         */
        getEmptySearchResultMessage: function(placeholders) {
            return this.noDataTemplate({
                hints: __(this.noDataTranslations.noResults, placeholders).replace('\n', '<br />')
            });
        },

        /**
         * @inheritDoc
         */
        getEmptySearchResultCustomMessage: function(message) {
            return this.noDataTemplate({
                hints: message
            });
        }
    });

    return FrontendGrid;
});

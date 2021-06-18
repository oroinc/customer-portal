define(function(require) {
    'use strict';

    const Grid = require('orodatagrid/js/datagrid/grid');
    const __ = require('orotranslation/js/translator');

    const FrontendGrid = Grid.extend({
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
        constructor: function FrontendGrid(options) {
            FrontendGrid.__super__.constructor.call(this, options);
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

            FrontendGrid.__super__._initColumns.call(this, options);
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
         * @inheritdoc
         */
        getEmptyGridMessage: function(placeholders) {
            const translation = this.noColumnsFlag
                ? this.noDataTranslations.noColumns : this.noDataTranslations.noEntities;

            return this.noDataTemplate({
                hints: __(translation, placeholders).replace('\n', '<br />')
            });
        },

        /**
         * @inheritdoc
         */
        getEmptyGridCustomMessage: function(message) {
            return this.noDataTemplate({
                hints: message
            });
        },

        /**
         * @inheritdoc
         */
        getEmptySearchResultMessage: function(placeholders) {
            return this.noDataTemplate({
                hints: __(this.noDataTranslations.noResults, placeholders).replace('\n', '<br />')
            });
        },

        /**
         * @inheritdoc
         */
        getEmptySearchResultCustomMessage: function(message) {
            return this.noDataTemplate({
                hints: message
            });
        }
    });

    return FrontendGrid;
});

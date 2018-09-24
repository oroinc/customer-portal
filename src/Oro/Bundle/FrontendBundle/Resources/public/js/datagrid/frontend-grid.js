define(function(require) {
    'use strict';

    var FrontendGrid;
    var Grid = require('orodatagrid/js/datagrid/grid');
    var $ = require('jquery');
    var _ = require('underscore');
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
         * Define no data block.
         */
        _defineNoDataBlock: function() {
            var placeholders = {
                entityHint: (this.entityHint || __('oro.datagrid.entityHint')).toLowerCase()
            };
            var message = _.isEmpty(this.collection.state.filters)
                ? this.noDataTranslations.noEntities : this.noDataTranslations.noResults;
            message = this.noColumnsFlag ? this.noDataTranslations.noColumns : message;

            this.$(this.selectors.noDataBlock).html($(this.noDataTemplate({
                hints: __(message, placeholders).replace('\n', '<br />')
            })));
        }
    });

    return FrontendGrid;
});

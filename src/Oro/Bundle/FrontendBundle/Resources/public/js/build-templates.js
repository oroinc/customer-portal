define(function(require) {
    'use strict';
    //use to include overridden templates into build
    require('text!orofrontend/default/templates/ui/modal-dialog.html');
    require('text!orofrontend/default/templates/ui/message-item.html');
    require('text!orofrontend/default/templates/datagrid/column-manager-collection.html');
    require('text!orofrontend/default/templates/datagrid/column-manager-item.html');
    require('text!orofrontend/default/templates/datagrid/column-manager-filter.html');
    require('text!orofrontend/default/templates/datagrid/column-manager.html');
    require('text!orofrontend/default/templates/datagrid/pagination-input.html');
    require('text!orofrontend/default/templates/datagrid/page-size.html');
    require('text!orofrontend/default/templates/datagrid/visible-items-counter.html');
    require('text!orofrontend/default/templates/datagrid/select-all-header-cell.html');
    require('text!orofrontend/default/templates/datagrid/select-row-cell.html');
    require('text!orofrontend/default/templates/datagrid/action-header-cell.html');
    require('text!orofrontend/default/templates/datagrid/grid-view-label.html');
    require('text!oropricing/templates/order/frontend/subtotals.html');
    require('text!oropricing/templates/order/frontend/totals.html');
    require('text!orofrontend/default/templates/filter/filters-container.html');
});

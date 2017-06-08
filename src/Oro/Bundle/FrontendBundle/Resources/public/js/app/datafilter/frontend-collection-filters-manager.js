define(function(require) {
    'use strict';

    var FrontendCollectionFiltersManager;
    var $ = require('jquery');
    var __ = require('orotranslation/js/translator');
    var CollectionFiltersManager = require('orofilter/js/collection-filters-manager');
    var MultiselectDecorator = require('orofrontend/js/app/datafilter/frontend-multiselect-decorator');

    FrontendCollectionFiltersManager = CollectionFiltersManager.extend({
        /**
         * Select widget object
         *
         * @property
         */
        MultiselectDecorator: MultiselectDecorator,

        /**
         * @inheritDoc
         */
        multiselectParameters: {
            classes: 'select-filter-widget',
            checkAllText: __('oro_frontend.filter_manager.checkAll'),
            uncheckAllText: __('oro_frontend.filter_manager.unCheckAll'),
            height: 'auto',
            menuWidth: 312
        },

        /** @property */
        events: {
            'click [data-role="close-filters"]': '_onClose'
        },

        /**
          * @inheritDoc
          */
        initialize: function(options) {
            FrontendCollectionFiltersManager.__super__.initialize.apply(this, arguments);
        },

        /**
         * Set design for filter manager button
         *
         * @protected
         */
        _setButtonDesign: function($button) {
            $button
                .addClass('btn btn--default btn--size-s')
                .find('span').addClass('fa--no-offset fa-plus hide-text');
        },

        /**
         *  Create html node
         *
         * @returns {*|jQuery|HTMLElement}
         * @private
         */
        _createButtonReset: function() {
            return $(
                '<div class="datagrid-manager__footer">' +
                    '<a href="javascript:void(0);" class="link" data-role="reset-filters">' +
                        '<i class="fa-refresh"></i>' + this.multiselectResetButtonLabel + '' +
                    '</a>' +
                '</div>'
            );
        },

        _onClose: function() {
            this.selectWidget.multiselect('instance').button.trigger('click');
        }
    });

    return FrontendCollectionFiltersManager;
});

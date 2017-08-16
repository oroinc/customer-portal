define(function(require) {
    'use strict';

    var FrontendCollectionFiltersManager;
    var $ = require('jquery');
    var __ = require('orotranslation/js/translator');
    var CollectionFiltersManager = require('orofilter/js/collection-filters-manager');
    var MultiselectDecorator = require('orofrontend/js/app/datafilter/frontend-multiselect-decorator');

    var config = require('module').config();
    config = $.extend(true, {
        templateData: {
            attributes: ''
        }
    }, config);

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
            menuWidth: 312,
            selectedText: __('oro_frontend.filter_manager.button_label')
        },

        /** @property */
        events: {
            'click [data-role="close-filters"]': '_onClose'
        },

        templateData: config.templateData,

        /**
         * Set design for filter manager button
         *
         * @protected
         */
        _setButtonDesign: function($button) {
            $button
                .addClass('filters-manager-trigger btn btn--default btn--size-s')
                .find('span')
                .addClass('fa--no-offset fa-plus hide-text');
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
        },

        getTemplateData: function() {
            var data = FrontendCollectionFiltersManager.__super__.getTemplateData.call(this);
            data = $.extend(data, this.templateData || {});
            return data;
        }
    });

    return FrontendCollectionFiltersManager;
});

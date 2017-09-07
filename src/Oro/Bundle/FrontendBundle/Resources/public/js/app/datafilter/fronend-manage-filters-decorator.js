define(function(require) {
    'use strict';

    var FrontendManageFiltersDecorator;
    var _ = require('underscore');
    var __ = require('orotranslation/js/translator');
    var $ = require('jquery');
    var FrontendMultiSelectDecorator = require('orofrontend/js/app/datafilter/frontend-multiselect-decorator');
    var config = require('module').config();

    config = $.extend(true, {
        hideHeader: false,
        themeName: 'default'
    }, config);

    FrontendManageFiltersDecorator = function(options) {
        FrontendMultiSelectDecorator.apply(this, arguments);
    };

    FrontendManageFiltersDecorator.prototype = _.extend(Object.create(FrontendMultiSelectDecorator.prototype), {
        /**
         * Save constructor after native extend
         *
         * @property {Object}
         */
        constructor: FrontendManageFiltersDecorator,

        /**
         * Flag for add update Dropdown markup
         *
         * @property {bool}
         */
        applyMarkup: true,

        /**
         * @inheritDoc
         */
        desingConfiguration: config,

        /**
         * @inheritDoc
         */
        multiselectFilterParameters: {
            label: __('oro_frontend.filter_manager.label'),
            placeholder: __('oro_frontend.filter_manager.placeholder')
        },

        /**
         * Update Dropdown design
         * @private
         */
        _setDropdownDesign: function() {
            var instance = this.multiselect('instance');

            if (this.applyMarkup) {
                this.updateDropdownMarkup(instance);
            }

            FrontendMultiSelectDecorator.prototype._setDropdownDesign.apply(this, arguments);
        },

        /**
         * Action on multiselect widget refresh
         */
        onRefresh: function() {
            var instance = this.multiselect('instance');
            this.updateFooterPosition(instance);

            FrontendMultiSelectDecorator.prototype.onRefresh.apply(this, arguments);
        },

        /**
         * Update Dropdown markup
         * @param {object} instance
         */
        updateDropdownMarkup: function(instance) {
            instance.headerLinkContainer
                .find('li')
                .addClass('datagrid-manager__actions-item')
                .filter(':first')
                .after(
                    $('<li/>', {
                        'class': 'datagrid-manager__actions-item'
                    }).append(
                        $('<span/>', {
                            'class': 'datagrid-manager__separator',
                            'text': '|'
                        })
                    )
                );
        },

        /**
         * Prepare design for Dropdown Header
         * @param {object} instance
         */
        setDropdownHeaderDesign: function(instance) {
            var checked = instance.getChecked().length;

            instance.header
                .append(
                    $('<span/>', {
                        'class': 'close',
                        'text': '×',
                        'data-role': 'close-filters'
                    })
                );

            instance.header
                .removeAttr('class')
                .addClass('datagrid-manager__header');

            instance.header
                .find('.ui-multiselect-none')
                .toggleClass('disabled', checked === 0);

            instance.header
                .find('.ui-multiselect-all')
                .toggleClass('disabled', checked === instance.inputs.length);

            instance.headerLinkContainer.addClass('datagrid-manager__actions');
        },

        /**
         * Places footer to the end of menu content that is needed after refresh of widget since lib removes old list
         * and appends new one to the end of content
         * @param {object} instance
         */
        updateFooterPosition: function(instance) {
            var $footerContainer = instance.menu.parent().find('.datagrid-manager__footer');
            var $checkboxContainer = instance.menu.find('.ui-multiselect-checkboxes');
            if ($footerContainer.length && $checkboxContainer.length) {
                $checkboxContainer.after($footerContainer);
            }
        },

        /**
         * Add Class for Dropdown Widget Container
         * @param {object} widget
         */
        addAdditionalClassesForContainer: function(widget) {
            FrontendMultiSelectDecorator.prototype.addAdditionalClassesForContainer.apply(this, arguments);

            widget.addClass('ui-rewrite');
        },

        /**
         * @param {object} instance
         */
        setDesignForCheckboxesDefaultTheme: function(instance) {
            FrontendMultiSelectDecorator.prototype.setDesignForCheckboxesDefaultTheme.apply(this, arguments);

            instance.menu
                .find('.datagrid-manager__list-item')
                .addClass('datagrid-manager__list-item--half');
        }
    });

    return FrontendManageFiltersDecorator;
});

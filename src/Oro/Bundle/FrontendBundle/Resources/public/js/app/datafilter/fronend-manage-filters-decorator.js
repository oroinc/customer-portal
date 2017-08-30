define(function(require) {
    'use strict';

    var FrontendManageFiltersDecorator;
    var _ = require('underscore');
    var __ = require('orotranslation/js/translator');
    var $ = require('jquery');
    var FrontendMultiselectDecorator = require('orofrontend/js/app/datafilter/frontend-multiselect-decorator');

    FrontendManageFiltersDecorator = function(options) {
        FrontendMultiselectDecorator.apply(this, arguments);
    };

    FrontendManageFiltersDecorator.prototype = _.extend(Object.create(FrontendMultiselectDecorator.prototype), {
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

            FrontendMultiselectDecorator.prototype._setDropdownDesign.apply(this, arguments);
        },

        /**
         * Action on multiselect widget refresh
         */
        onRefresh: function() {
            var instance = this.multiselect('instance');
            this.updateFooterPosition(instance);

            FrontendMultiselectDecorator.prototype.onRefresh.apply(this, arguments);
        },

        /**
         * Update Dropdown markup
         * @param {object} instance
         */
        updateDropdownMarkup: function(instance) {
            instance.menu
                .wrap(
                    $('<div/>', {'class': 'datagrid-manager'})
                );

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
         * Prepare design for checkboxes
         * @param {object} instance
         */
        setCheckboxesDesign: function(instance) {
            // TOdo fix me
            instance.menu.children('.ui-multiselect-checkboxes')
                .find('li')
                .addClass('filter-dropdown_+_option--half-width');

            FrontendMultiselectDecorator.prototype.setCheckboxesDesign.apply(this, arguments);
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
        }
    });

    return FrontendManageFiltersDecorator;
});

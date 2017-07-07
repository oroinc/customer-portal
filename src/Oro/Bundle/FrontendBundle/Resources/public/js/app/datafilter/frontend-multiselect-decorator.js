define(function(require) {
    'use strict';

    var FrontendMultiselectDecorator;
    var _ = require('underscore');
    var __ = require('orotranslation/js/translator');
    var $ = require('jquery');
    var MultiselectDecorator = require('orofilter/js/multiselect-decorator');

    FrontendMultiselectDecorator = function(options) {
        MultiselectDecorator.apply(this, arguments);
    };

    FrontendMultiselectDecorator.prototype = _.extend(Object.create(MultiselectDecorator.prototype), {
        /**
         * Save constructor after native extend
         *
         * @property {Object}
         */
        constructor: FrontendMultiselectDecorator,

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
            var $widget = this.getWidget();
            var instance = this.multiselect('instance');

            if (this.applyMarkup) {
                this.updateDropdownMarkup(instance);
                this.applyMarkup = false;
            }

            this.setCheckboxesDesign(instance);

            $widget
                .removeAttr('class')
                .addClass('dropdown-menu ui-rewrite');
        },

        onOpenDropdown: function() {
            var instance = this.multiselect('instance');
            this.setCheckboxesDesign(instance);
            return MultiselectDecorator.prototype.onOpenDropdown.apply(this, arguments);
        },

        /**
         * Action on multiselect widget refresh
         */
        onRefresh: function() {
            var instance = this.multiselect('instance');
            this.setDropdownHeaderDesign(instance);
            this.setCheckboxesDesign(instance);
            this.updateFooterPosition(instance);
        },

        /**
         * Update Dropdown markup
         * @param {object} instance
         */
        updateDropdownMarkup: function(instance) {
            instance.menu
                .wrap(
                    $('<div/>', {'class': 'datagrid-manager'})
                )
                .find('.ui-multiselect-filter')
                .removeAttr('class');

            instance.header
                .append(
                    $('<span/>', {
                        'class': 'close',
                        'text': '×',
                        'data-role': 'close-filters'
                    })
                )
                .find('input')
                .wrap(
                    $('<div/>', {'class': 'datagrid-manager-search empty'})
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
         * Set design for view
         *
         * @param {Backbone.View} view
         */
        setViewDesign: function(view) {
            view.$('.ui-multiselect')
                .removeClass('ui-widget')
                .removeClass('ui-state-default');
            view.$('.ui-multiselect span.ui-icon').remove();
        },

        /**
         * Prepare design for Dropdown Header
         * @param {object} instance
         */
        setDropdownHeaderDesign: function(instance) {
            var checked = instance.getChecked().length;

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
         * Prepare design for checkboxes
         * @param {object} instance
         */
        setCheckboxesDesign: function(instance) {
            var $icon = instance.labels.find('.custom-checkbox__icon');
            instance.menu.children('.ui-multiselect-checkboxes')
                .removeClass('ui-helper-reset')
                .addClass('datagrid-manager__checkboxes ui-rewrite')
                .find('li')
                .addClass('datagrid-manager__checkboxes-item');

            instance.labels
                .addClass('custom-checkbox absolute')
                .find('span')
                .addClass('custom-checkbox__text');

            if (!$icon.length) {
                instance.inputs
                    .addClass('custom-checkbox__input ui-rewrite')
                    .after($('<i/>', {'class': 'custom-checkbox__icon'}));
            }
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

    return FrontendMultiselectDecorator;
});

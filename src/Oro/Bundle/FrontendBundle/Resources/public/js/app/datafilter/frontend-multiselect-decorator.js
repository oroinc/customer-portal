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
         * Update Dropdown design
         * @private
         */
        _setDropdownDesign: function() {
            var widget = this.getWidget();
            var instance = this.multiselect('instance');

            if (this.applyMarkup) {
                this.setDropdownWidgetContainer(widget);
                this.setDropdownHeaderDesign(instance);
                this.setDropdownHeaderSearchDesign(instance);
                this.applyMarkup = false;
            }

            if (instance.options.multiple) {
                this.setCheckboxesDesign(instance);
            }

        },

        onOpenDropdown: function() {
            var instance = this.multiselect('instance');
            if (instance.options.multiple) {
                this.setCheckboxesDesign(instance);
            }

            return MultiselectDecorator.prototype.onOpenDropdown.apply(this, arguments);
        },

        /**
         * Action on multiselect widget refresh
         */
        onRefresh: function() {
            var instance = this.multiselect('instance');
            this.setDropdownHeaderDesign(instance);
            if (instance.options.multiple) {
                this.setCheckboxesDesign(instance);
            }
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
         * Prepare design for checkboxes
         * @param {object} instance
         */
        setCheckboxesDesign: function(instance) {
            var $icon = instance.labels.find('.custom-checkbox__icon');

            instance.menu.children('.ui-multiselect-checkboxes')
                .removeClass('ui-helper-reset')
                .addClass('filter-dropdown__options-list ui-rewrite')
                .find('li')
                .addClass('filter-dropdown__option');

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
         * Prepare design for Dropdown Widget Container
         * @param {object} widget
         */
        setDropdownWidgetContainer: function(widget) {
            widget
                .removeAttr('class')
                .addClass('filter-dropdown dropdown-menu ui-rewrite');
        },

        /**
         * Prepare design for Dropdown Header
         * @param {object} instance
         */
        setDropdownHeaderDesign: function(instance) {
            instance.header
                .removeAttr('class')
                .addClass('filter-dropdown__header');
        },

        /**
         * Prepare design for Dropdown Header Search
         * @param {object} instance
         */
        setDropdownHeaderSearchDesign: function(instance) {
            instance.header
                .find('input')
                .wrap(
                    $('<div/>', {'class': 'filter-dropdown__search empty'})
                );
            instance.header
                .find('.ui-multiselect-filter')
                .removeAttr('class');
        }
    });

    return FrontendMultiselectDecorator;
});

define(function(require, exports, module) {
    'use strict';

    const _ = require('underscore');
    const __ = require('orotranslation/js/translator');
    const $ = require('jquery');
    const MultiSelectDecorator = require('orofilter/js/multiselect-decorator');
    let config = require('module-config').default(module.id);

    config = $.extend(true, {
        hideHeader: false,
        themeName: 'filter-default',
        additionalClass: true
    }, config);

    const FrontendMultiSelectDecorator = function(options) {
        const params = _.pick(options.parameters, ['additionalClass', 'hideHeader', 'themeName', 'listAriaLabel']);

        if (!_.isEmpty(params)) {
            this.parameters = _.extend({}, this.parameters, params);
        }

        MultiSelectDecorator.call(this, options);
    };

    FrontendMultiSelectDecorator.prototype = _.extend(Object.create(MultiSelectDecorator.prototype), {
        /**
         * Save constructor after native extend
         *
         * @property {Object}
         */
        constructor: FrontendMultiSelectDecorator,

        /**
         * Flag for add update Dropdown markup
         *
         * @property {bool}
         */
        applyMarkup: true,

        /**
         * Optional parameters of multiselect widget
         * @property {object}
         */
        parameters: {
            additionalClass: config.additionalClass,
            hideHeader: config.hideHeader,
            themeName: config.themeName
        },

        /**
         * @inheritdoc
         */
        multiselectFilterParameters: {
            placeholder: __('oro_frontend.filters.multiselect.placeholder'),
            searchAriaLabel: __('oro_frontend.filters.multiselect.aria_label')
        },

        /**
         * Update Dropdown design
         * @private
         */
        _setDropdownDesign: function() {
            const widget = this.getWidget();
            const instance = this.multiselect('instance');

            if (!_.isObject(instance)) {
                return;
            }

            if (this.parameters.hideHeader) {
                instance.header.hide();
            }

            switch (this.parameters.themeName) {
                case 'all-at-once':
                    this.applyAllToOnceTheme(widget, instance);
                    break;
                default:
                    this.applyDefaultTheme(widget, instance);
                    break;
            }

            this.appendNoFoundTemplate();
        },

        /**
         * @param {object} widget
         * @param {object} instance
         */
        applyDefaultTheme: function(widget, instance) {
            this.applyBaseMarkup(widget, instance);
            this.setDesignForCheckboxesDefaultTheme(instance);
        },

        /**
         * @param {object} widget
         * @param {object} instance
         */
        applyAllToOnceTheme: function(widget, instance) {
            this.applyBaseMarkup(widget, instance);
            this.setDesignForCheckboxesAllToOnceTheme(instance);
        },

        /**
         * @param {object} widget
         * @param {object} instance
         */
        applyBaseMarkup: function(widget, instance) {
            if (this.applyMarkup) {
                this.applyMarkup = false;

                this.addAdditionalClassesForContainer(widget);
                this.setDropdownWidgetContainer(instance);
                this.setDropdownHeaderDesign(instance);
                this.setDropdownHeaderSearchDesign(instance);
            }
        },

        /**
         * @param {object} instance
         */
        setDesignForCheckboxesDefaultTheme: function(instance) {
            instance.menu
                .children('.ui-multiselect-checkboxes')
                .removeClass('ui-helper-reset')
                .addClass('datagrid-manager__list ui-rewrite')
                .find('li')
                .addClass('datagrid-manager__list-item');

            instance.labels.addClass('checkbox-label');
        },

        /**
         * @param {object} instance
         */
        setDesignForCheckboxesAllToOnceTheme: function(instance) {
            instance.menu
                .children('.ui-multiselect-checkboxes')
                .addClass('filters-dropdown')
                .find('li')
                .addClass('filters-dropdown__items filters-dropdown__items--pallet');

            instance.labels.addClass('filters-dropdown__labels');
            instance.inputs.addClass('filters-dropdown__inputs');
        },

        /**
         * Action on multiselect widget refresh
         */
        onRefresh: function() {
            if (_.isFunction(this.setActionsState)) {
                const instance = this.multiselect('instance');
                this.setActionsState(instance);
            }

            this._setDropdownDesign();
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
         * Add wrapper for Dropdown Widget Menu
         * @param {object} instance
         */
        setDropdownWidgetContainer: function(instance) {
            instance.menu
                .wrap(
                    $('<div></div>', {'class': 'datagrid-manager', 'data-cid': `menu-${this.cid}`})
                );
        },

        /**
         * Add Class for Dropdown Widget Container
         * @param {object} widget
         */
        addAdditionalClassesForContainer: function(widget) {
            if (this.parameters.additionalClass) {
                widget
                    .removeAttr('class')
                    .addClass(`${this.parameters.themeName} dropdown-menu`);
            }
        },

        /**
         * Prepare design for Dropdown Header
         * @param {object} instance
         */
        setDropdownHeaderDesign: function(instance) {
            instance.header
                .removeAttr('class')
                .addClass('datagrid-manager__header');
        },

        /**
         * Prepare design for Dropdown Header Search
         * @param {object} instance
         */
        setDropdownHeaderSearchDesign: function(instance) {
            instance.header
                .find('input')
                .addClass('input input--full input--size-s')
                .wrap(
                    $('<div></div>', {'class': 'datagrid-manager-search empty'})
                );

            instance.header
                .find('.ui-multiselect-filter')
                .contents()
                .eq(0)
                .filter((i, el) => el.nodeType === Node.TEXT_NODE)
                .wrap(
                    $('<h5></h5>', {'class': 'datagrid-manager__title'})
                );

            instance.header
                .find('.ui-multiselect-filter')
                .removeAttr('class');

            instance.header
                .find('.ui-multiselect-close')
                .addClass('hide');
        },

        dispose() {
            $(`[data-cid="menu-${this.cid}"]`).remove();

            return MultiSelectDecorator.prototype.dispose.call(this);
        }
    });

    return FrontendMultiSelectDecorator;
});

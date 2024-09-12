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
        additionalClass: true,
        resetButton: null
    }, config);

    const FrontendMultiSelectDecorator = function(options) {
        const params = _.pick(options.parameters,
            ['additionalClass', 'hideHeader', 'themeName', 'listAriaLabel', 'resetButton']
        );

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

        listItemClasses: 'datagrid-manager__list-item',

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
            this.setDesignForFooter(widget, instance);
        },

        /**
         * @param {object} widget
         * @param {object} instance
         */
        applyAllToOnceTheme: function(widget, instance) {
            this.applyBaseMarkup(widget, instance);
            this.setDesignForCheckboxesAllToOnceTheme(instance);
            this.setDesignForFooter(widget, instance);
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
                this.setDropdownFooterDesign(widget, instance);
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
                .addClass(this.listItemClasses);

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
                    .attr('class', null)
                    .addClass(`${this.parameters.themeName} dropdown-menu`);
            }
        },

        /**
         * Prepare design for Dropdown Header
         * @param {object} instance
         */
        setDropdownHeaderDesign: function(instance) {
            instance.header
                .attr('class', null)
                .addClass('datagrid-manager__header');
        },

        /**
         * Prepare design for Dropdown Header Search
         * @param {object} instance
         */
        setDropdownHeaderSearchDesign: function(instance) {
            if (this.maxItemsForShowSearchBar > 0 &&
                instance.element.children(':enabled').length > this.maxItemsForShowSearchBar) {
                const searchIcon = _.macros('oroui::renderIcon')({
                    name: 'search',
                    extraClass: 'datagrid-manager-search__icon'
                });

                instance.header
                    .find('input[type="search"]')
                    .addClass('input input--full')
                    .wrap(
                        $('<div></div>', {'class': 'datagrid-manager-search empty'})
                    );

                instance.header
                    .find('input[type="search"]')
                    .after(searchIcon);
            } else {
                instance.header
                    .find('input[type="search"]')
                    .remove();
            }

            instance.header
                .find('.ui-multiselect-filter')
                .contents()
                .first()
                .filter((i, el) => el.nodeType === Node.TEXT_NODE)
                .wrap(
                    $(instance.element.closest('.toggle-mode').length
                        ? '<h3/>'
                        : '<h5/>', {'class': 'datagrid-manager__title'})
                );

            instance.header.find('.datagrid-manager__title').parent().addClass('datagrid-manager__title-container');
            instance.header.find('.datagrid-manager__title').after(this.createIconButton('close'));
            instance.header.find('.datagrid-manager__title').before(this.createIconButton('arrow-left'));

            instance.header
                .find('.ui-multiselect-filter')
                .attr('class', null);

            instance.header
                .find('.ui-multiselect-close')
                .addClass('hide');
        },

        setDropdownFooterDesign(widget, instance) {
            instance.footer = $('<div />', {
                'class': 'datagrid-manager__footer'
            });

            if (this.parameters.resetButton) {
                this.setDesignForResetButton(widget, instance);
            }

            this.setDesignForFooter(widget, instance);
        },

        setDesignForFooter(widget, instance) {
            instance.footer.appendTo(widget);
        },

        setDesignForResetButton(widget, instance) {
            instance.resetButton = $('<button />', {
                'class': 'btn btn--flat',
                ...this.parameters.resetButton.attr || {}
            }).text(this.parameters.resetButton.label || 'Reset');

            instance.resetButton.on(`click`, event => {
                if (typeof this.parameters.resetButton.onClick === 'function') {
                    this.parameters.resetButton.onClick(event);
                }
            });

            instance.footer.append(instance.resetButton);
        },

        toggleVisibilityResetButton(hidden) {
            const instance = this.multiselect('instance');
            if (instance.resetButton) {
                instance.resetButton.toggleClass('hidden', hidden);
            }
        },

        createIconButton(icon) {
            return `<button class="btn btn--icon btn--plain datagrid-manager__title--back-btn" data-role="close">
    ${_.macros('oroui::renderIcon')({
        name: icon
    })}
</button>`;
        },

        dispose() {
            $(`[data-cid="menu-${this.cid}"]`).remove();

            return MultiSelectDecorator.prototype.dispose.call(this);
        }
    });

    return FrontendMultiSelectDecorator;
});

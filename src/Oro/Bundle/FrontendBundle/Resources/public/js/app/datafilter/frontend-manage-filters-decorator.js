define(function(require, exports, module) {
    'use strict';

    const _ = require('underscore');
    const __ = require('orotranslation/js/translator');
    const $ = require('jquery');
    const FrontendMultiSelectDecorator = require('orofrontend/js/app/datafilter/frontend-multiselect-decorator');
    const itemActionCheckboxTemplate = require('tpl-loader!orofrontend/templates/multiselect-item-action-checkbox.html');
    let config = require('module-config').default(module.id);

    config = $.extend(true, {
        hideHeader: false,
        themeName: 'default',
        additionalClass: true
    }, config);

    const FrontendManageFiltersDecorator = function(options) {
        FrontendMultiSelectDecorator.call(this, options);
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
         * @inheritdoc
         */
        parameters: config,

        /**
         * @inheritdoc
         */
        multiselectFilterParameters: {
            label: __('oro_frontend.filter_manager.label'),
            placeholder: __('oro_frontend.filter_manager.placeholder'),
            searchAriaLabel: __('oro_frontend.filter_manager.searchAriaLabel')
        },

        listItemClasses: 'datagrid-manager__list-item datagrid-manager__list-item--offset',

        /**
         * Update Dropdown design
         * @private
         */
        _setDropdownDesign: function() {
            const instance = this.multiselect('instance');

            if (this.applyMarkup) {
                this.updateDropdownMarkup(instance);
            }

            FrontendMultiSelectDecorator.prototype._setDropdownDesign.call(this);
        },

        /**
         * Action on multiselect widget refresh
         */
        onRefresh: function() {
            const instance = this.multiselect('instance');
            this.updateFooterPosition(instance);

            FrontendMultiSelectDecorator.prototype.onRefresh.call(this);
        },

        /**
         * Update Dropdown markup
         * @param {object} instance
         */
        updateDropdownMarkup: function(instance) {
            instance.headerLinkContainer
                .find('li')
                .addClass('datagrid-manager__actions-item')
                .each((index, element) => {
                    const $element = $(element);
                    const [name] = [...element.firstChild.classList].filter(cls => cls.startsWith('ui-multiselect-'));

                    $element
                        .find('[role="button"]')
                        .html(itemActionCheckboxTemplate({
                            name,
                            title: element.innerText
                        }));
                });
        },

        /**
         * Prepare design for Dropdown Header
         * @param {object} instance
         */
        setDropdownHeaderDesign: function(instance) {
            instance.header
                .attr('class', null)
                .addClass('datagrid-manager__header');

            instance.menu
                .find('[data-role="reset-filters"]')
                .addClass('btn btn--flat');

            this.setActionsState(instance);

            instance.headerLinkContainer.addClass('datagrid-manager__actions');
        },

        /**
         * Set enable/disable state for actions of filters manager
         * @param {object} instance
         */
        setActionsState: function(instance) {
            const value = instance.element.val();
            const selectedAll = value.length === instance.element.children(':enabled').length;
            const isIndeterminate =
                value.length > 0 && value.length < instance.element.children(':enabled').length;
            const valueChanged = instance.initialValue.length !== value.length ||
                !instance.initialValue.every(val => value.includes(val));

            const actions = [{
                $el: instance.header.find('.ui-multiselect-none'),
                toApply: !selectedAll
            }, {
                $el: instance.header.find('.ui-multiselect-all'),
                toApply: selectedAll
            }, {
                $el: instance.menu.find('[data-role="reset-filters"]'),
                toApply: !valueChanged
            }];

            for (const {$el, toApply} of actions) {
                $el.toggleClass('disabled', toApply);

                if ($el.is(':button')) {
                    $el.attr('disabled', toApply);
                } else if ($el.is('label')) {
                    $el.toggleClass('hidden', toApply);

                    $el.find('input[type="checkbox"]').prop({
                        indeterminate: isIndeterminate,
                        checked: selectedAll
                    });
                } else {
                    $el.attr({
                        'tabindex': toApply ? '-1' : null,
                        'role': 'button',
                        'href': toApply ? null : '#',
                        'aria-disabled': toApply ? true : null
                    });
                }
            }
        },

        /**
         * Places footer to the end of menu content that is needed after refresh of widget since lib removes old list
         * and appends new one to the end of content
         * @param {object} instance
         */
        updateFooterPosition: function(instance) {
            const $footerContainer = instance.menu.parent().find('.datagrid-manager__footer');
            const $checkboxContainer = instance.menu.find('.ui-multiselect-checkboxes');
            if ($footerContainer.length && $checkboxContainer.length) {
                $checkboxContainer.after($footerContainer);
            }
        },

        /**
         * Add Class for Dropdown Widget Container
         * @param {object} widget
         */
        addAdditionalClassesForContainer: function(widget) {
            FrontendMultiSelectDecorator.prototype.addAdditionalClassesForContainer.call(this, widget);

            widget.addClass('ui-rewrite');
        }
    });

    return FrontendManageFiltersDecorator;
});

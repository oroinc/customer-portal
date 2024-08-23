define(function(require, exports, module) {
    'use strict';

    const $ = require('jquery');
    const _ = require('underscore');
    const __ = require('orotranslation/js/translator');
    const CollectionFiltersManager = require('orofilter/js/collection-filters-manager');
    const MultiselectDecorator = require('orofrontend/js/app/datafilter/frontend-manage-filters-decorator');
    const FrontendCollapsableHintsView = require('./frontend-collapsable-hints-view').default;
    const ScrollShadowView = require('orofrontend/js/app/views/scroll-shadow-view').default;

    let config = require('module-config').default(module.id);
    config = _.extend({
        templateData: {
            attributes: ''
        },
        enableMultiselectWidget: true
    }, config);

    const FrontendCollectionFiltersManager = CollectionFiltersManager.extend({
        /**
         * Select widget object
         *
         * @property
         */
        MultiselectDecorator: MultiselectDecorator,

        /**
         * @inheritdoc
         */
        enableMultiselectWidget: true,

        multiselectResetButtonLabel: __('oro_frontend.filter_manager.resetFilter'),

        enableScrollContainerShadow: false,

        /**
         * @inheritdoc
         */
        multiselectParameters: {
            classes: 'select-filter-widget',
            checkAllText: __('oro_frontend.filter_manager.checkAll'),
            uncheckAllText: __('oro_frontend.filter_manager.unCheckAll'),
            height: 'auto',
            menuWidth: 312,
            selectedText: __('oro_frontend.filter_manager.button_label'),
            noneSelectedText: __('oro_frontend.filter_manager.button_label'),
            listAriaLabel: __('oro_frontend.filter_manager.listAriaLabel')
        },

        /** @property */
        events: {
            'click [data-role="close"]': '_onClose'
        },

        /**
         * @inheritdoc
         */
        templateData: config.templateData,

        optionNames: CollectionFiltersManager.prototype.optionNames.concat([
            'fullscreenTemplate', 'filtersStateElement', 'filterEnableValueBadge', 'allowClearButtonInFilter',
            'hintsToggledStatus', 'enableScrollContainerShadow'
        ]),

        hintsExpanded: false,

        /**
         * @inheritdoc
         */
        constructor: function FrontendCollectionFiltersManager(options) {
            FrontendCollectionFiltersManager.__super__.constructor.call(this, options);
        },

        /**
         * @inheritdoc
         */
        render: function() {
            FrontendCollectionFiltersManager.__super__.render.call(this);

            if (this.filtersStateElement && this.filtersStateElement instanceof $) {
                this.filtersStateElement.remove();
            }

            this.finallyOfRender();
            return this;
        },

        /**
         * Set design for filter manager button
         *
         * @protected
         */
        _setButtonDesign: function($button) {
            $button
                .attr({
                    'class': `btn btn--neutral ${$button.attr('class')} filters-manager-trigger`,
                    'title': __('oro_frontend.filter_manager.label'),
                    'aria-label': __('oro_frontend.filter_manager.button_aria_label')
                })
                .prepend(_.macros('oroui::renderIcon')({
                    name: 'settings'
                }));
        },

        /**
         *  Create html node
         *
         * @returns {*|jQuery|HTMLElement}
         * @private
         */
        _createButtonReset: function() {
            // Use link to keep focus even on disabled state
            const icon = _.macros('oroui::renderIcon')({
                name: 'undo'
            });

            return $(`
                <div class="datagrid-manager__footer">
                    <a href="#" role="button" class="btn"
                        data-role="reset-filters">
                        ${icon}${this.multiselectResetButtonLabel}
                    </a>
                </div>
            `);
        },

        _onClose: function() {
            if (this.selectWidget) {
                this.selectWidget.multiselect('instance').button.trigger('click');
            }
        },

        /**
         * @inheritdoc
         */
        getTemplateData: function() {
            let data = FrontendCollectionFiltersManager.__super__.getTemplateData.call(this);
            data = $.extend(data, this.templateData || {});
            return data;
        },

        /**
         * @inheritdoc
         */
        _onCollectionReset: function(collection) {
            if (!_.isMobile()) {
                FrontendCollectionFiltersManager.__super__._onCollectionReset.call(this, collection);
            }
        },

        _onFilterUpdated() {
            FrontendCollectionFiltersManager.__super__._onFilterUpdated.call(this);
            this.subview('collapsableHints') && this.subview('collapsableHints').update();
        },

        _onFilterChanged() {
            FrontendCollectionFiltersManager.__super__._onFilterChanged.call(this);
            this.subview('collapsableHints') && this.subview('collapsableHints').update();
        },

        _onFilterDisabled(filter) {
            FrontendCollectionFiltersManager.__super__._onFilterDisabled.call(this, filter);
            this.subview('collapsableHints') && this.subview('collapsableHints').update();
        },

        finallyOfRender: function() {
            if (this.$el.data('layout') === 'separate') {
                this.initLayout();
            }

            this.subview('collapsableHints', new FrontendCollapsableHintsView({
                autoRender: true,
                filterManager: this,
                filters: this.filters,
                container: this.getHintContainer(),
                toggled: this.hintsExpanded
            }));

            this.listenTo(
                this.subview('collapsableHints'),
                'hints:change-visibility',
                toggledStatus => this.hintsExpanded = toggledStatus
            );

            if (this.renderMode === 'toggle-mode' && this.enableScrollContainerShadow) {
                this.subview('scroll-shadow', new ScrollShadowView({
                    el: this.el,
                    scrollTarget: '[data-filters-items]'
                }));
            }
        }
    });

    return FrontendCollectionFiltersManager;
});

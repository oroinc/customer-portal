define(function(require, exports, module) {
    'use strict';

    const $ = require('jquery');
    const _ = require('underscore');
    const __ = require('orotranslation/js/translator');
    const viewportManager = require('oroui/js/viewport-manager');
    const CollectionFiltersManager = require('orofilter/js/collection-filters-manager');
    const MultiselectDecorator = require('orofrontend/js/app/datafilter/frontend-manage-filters-decorator');
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

        /**
         * @inheritdoc
         */
        renderMode: '',

        /**
         * @inheritdoc
         */
        constructor: function FrontendCollectionFiltersManager(options) {
            FrontendCollectionFiltersManager.__super__.constructor.call(this, options);
        },

        /**
         * @inheritdoc
         */
        initialize: function(options) {
            this._updateRenderMode();
            FrontendCollectionFiltersManager.__super__.initialize.call(this, options);
        },

        /**
         * @inheritdoc
         */
        render: function() {
            FrontendCollectionFiltersManager.__super__.render.call(this);
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
                    'class': `${$button.attr('class')} filters-manager-trigger btn btn--default btn--size-s`,
                    'title': __('oro_frontend.filter_manager.label'),
                    'aria-label': __('oro_frontend.filter_manager.button_aria_label')
                })
                .find('span')
                .attr({
                    'aria-hidden': true,
                    'class': 'fa-plus fa--no-offset hide-text'
                });
        },

        /**
         *  Create html node
         *
         * @returns {*|jQuery|HTMLElement}
         * @private
         */
        _createButtonReset: function() {
            return $(`
                <div class="datagrid-manager__footer">
                    <button class="btn btn--link btn--no-x-offset btn--no-y-offset"
                        type="button" data-role="reset-filters">
                        <span class="fa-refresh" aria-hidden="true"></span>${this.multiselectResetButtonLabel}
                    </button>
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

        /**
         * Update render mode for filters manager
         *
         * @protected
         */
        _updateRenderMode: function() {
            const breakpoints = {
                screenType: 'tablet'
            };

            if (viewportManager.isApplicable(breakpoints)) {
                this.renderMode = 'toggle-mode';
            }
        },

        finallyOfRender: function() {
            if (this.$el.data('layout') === 'separate') {
                this.initLayout();
            }
        }
    });

    return FrontendCollectionFiltersManager;
});

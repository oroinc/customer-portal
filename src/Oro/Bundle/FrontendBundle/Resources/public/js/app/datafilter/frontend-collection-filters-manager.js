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
        }
    }, config);

    const FrontendCollectionFiltersManager = CollectionFiltersManager.extend({
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
            selectedText: __('oro_frontend.filter_manager.button_label'),
            noneSelectedText: __('oro_frontend.filter_manager.button_label')
        },

        /** @property */
        events: {
            'click [data-role="close"]': '_onClose'
        },

        /**
         * @inheritDoc
         */
        templateData: config.templateData,

        /**
         * @inheritDoc
         */
        renderMode: '',

        /**
         * @inheritDoc
         */
        constructor: function FrontendCollectionFiltersManager(options) {
            FrontendCollectionFiltersManager.__super__.constructor.call(this, options);
        },

        /**
         * @inheritDoc
         */
        initialize: function(options) {
            this._updateRenderMode();
            FrontendCollectionFiltersManager.__super__.initialize.call(this, options);
        },

        /**
         * @inheritDoc
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
            return $(
                '<div class="datagrid-manager__footer">' +
                    '<a href="#" class="link" data-role="reset-filters">' +
                        '<i class="fa-refresh"></i>' + this.multiselectResetButtonLabel + '' +
                    '</a>' +
                '</div>'
            );
        },

        _onClose: function() {
            this.selectWidget.multiselect('instance').button.trigger('click');
        },

        /**
         * @inheritDoc
         */
        getTemplateData: function() {
            let data = FrontendCollectionFiltersManager.__super__.getTemplateData.call(this);
            data = $.extend(data, this.templateData || {});
            return data;
        },

        /**
         * @inheritDoc
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

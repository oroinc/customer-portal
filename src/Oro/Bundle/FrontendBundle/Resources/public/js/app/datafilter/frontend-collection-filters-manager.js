define(function(require) {
    'use strict';

    var FrontendCollectionFiltersManager;
    var $ = require('jquery');
    var _ = require('underscore');
    var __ = require('orotranslation/js/translator');
    var viewportManager = require('oroui/js/viewport-manager');
    var CollectionFiltersManager = require('orofilter/js/collection-filters-manager');
    var MultiselectDecorator = require('orofrontend/js/app/datafilter/fronend-manage-filters-decorator');

    var config = require('module').config();
    config = _.extend({
        templateData: {
            attributes: ''
        }
    }, config);

    FrontendCollectionFiltersManager = CollectionFiltersManager.extend({
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
            'click [data-role="close-filters"]': '_onClose'
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
        initialize: function(options) {
            this._updateRenderMode();
            FrontendCollectionFiltersManager.__super__.initialize.apply(this, arguments);
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
                .addClass('filters-manager-trigger btn btn--default btn--size-s')
                .find('span')
                .addClass('fa--no-offset fa-plus hide-text');
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
                    '<a href="javascript:void(0);" class="link" data-role="reset-filters">' +
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
            var data = FrontendCollectionFiltersManager.__super__.getTemplateData.call(this);
            data = $.extend(data, this.templateData || {});
            return data;
        },

        /**
         * @inheritDoc
         */
        _onCollectionReset: function(collection) {
            if (!_.isMobile()) {
                FrontendCollectionFiltersManager.__super__._onCollectionReset.apply(this, arguments);
            }
        },

        /**
         * Update render mode for filters manager
         *
         * @protected
         */
        _updateRenderMode: function() {
            var breakpoints = ['tablet', 'tablet-small', 'mobile-landscape', 'mobile'];

            if (_.contains(breakpoints, viewportManager.getViewport().type)) {
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

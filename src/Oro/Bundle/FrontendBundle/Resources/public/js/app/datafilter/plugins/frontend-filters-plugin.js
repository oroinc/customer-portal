define(function(require, exports, module) {
    'use strict';

    const _ = require('underscore');
    const __ = require('orotranslation/js/translator');
    const FullScreenFiltersAction = require('orofrontend/js/app/datafilter/actions/fullscreen-filters-action');
    const FiltersTogglePlugin = require('orofilter/js/plugins/filters-toggle-plugin');
    const FullscreenFilters = require('orofrontend/js/app/datafilter/fullscreen-filters').default;
    const config = require('module-config').default(module.id);
    const launcherOptions = _.extend({
        className: 'btn',
        icon: 'filter',
        label: __('oro.filter.datagrid-toolbar.filters'),
        ariaLabel: __('oro.filter.datagrid-toolbar.aria_label')
    }, config.launcherOptions || {});

    const FrontendFiltersTogglePlugin = FiltersTogglePlugin.extend({
        /**
         * @inheritdoc
         */
        constructor: function FrontendFiltersTogglePlugin(main, options) {
            FrontendFiltersTogglePlugin.__super__.constructor.call(this, main, options);
        },

        initialize(options) {
            FrontendFiltersTogglePlugin.__super__.initialize.call(this, options);

            this.fullscreenFilters = new FullscreenFilters({datagrid: this.main});
            this.listenToOnce(this.main, 'filterManager:connected', () => {
                this.fullscreenFilters.onceFilterManagerConnected();
            });
        },

        onBeforeToolbarInit(toolbarOptions) {
            this.addAction(toolbarOptions);
        },

        addAction(toolbarOptions) {
            let options = {
                datagrid: this.main,
                launcherOptions: launcherOptions,
                order: config.order || 50,
                fullscreenFilters: this.fullscreenFilters
            };
            let Action = FullScreenFiltersAction;

            if (_.isObject(toolbarOptions.customAction)) {
                if (toolbarOptions.customAction.constructor) {
                    Action = toolbarOptions.customAction.constructor;
                }

                options = Object.assign({}, options, toolbarOptions.customAction.options);
            }

            toolbarOptions.addToolbarAction(new Action(options));
        },

        dispose() {
            if (this.disposed) {
                return;
            }

            this.disable();

            if (this.fullscreenFilters && !this.fullscreenFilters.disposed) {
                this.fullscreenFilters.dispose();
                delete this.fullscreenFilters;
            }

            FrontendFiltersTogglePlugin.__super__.dispose.call(this);
        }
    });
    return FrontendFiltersTogglePlugin;
});

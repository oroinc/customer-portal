define(function(require, exports, module) {
    'use strict';

    const $ = require('jquery');
    const _ = require('underscore');
    const __ = require('orotranslation/js/translator');
    const ShowComponentAction = require('oro/datagrid/action/show-component-action');
    const DatagridSettingsPlugin = require('orodatagrid/js/app/plugins/grid/datagrid-settings-plugin');
    const DatagridSettingView = require('orodatagrid/js/app/views/grid/datagrid-settings-view');

    let config = require('module-config').default(module.id);
    config = $.extend(true, {
        icon: 'columns',
        actionClassNames: 'btn--neutral',
        wrapperClassName: 'datagrid-settings',
        label: __('oro_frontend.datagrid.settings.label'),
        ariaLabel: __('oro.datagrid.settings.title_aria_label'),
        launcherMode: 'icon-text',
        attributes: {
            'data-placement': 'bottom-end',
            'data-modifiers': JSON.stringify({flip: {enabled: false}}),
            'data-responsive-styler': '',
            'data-input-widget-options': JSON.stringify({
                responsive: {
                    'mobile-big': {
                        classes: 'dropdown-item text-nowrap',
                        disposeTooltip: true
                    }
                }
            }),
            'data-tooltip': JSON.stringify({
                placement: 'top',
                trigger: 'hover'
            })
        }
    }, config);

    /**
     * @class FrontendDatagridSettingsPlugin
     * @extends DatagridSettingsPlugin
     */
    const FrontendDatagridSettingsPlugin = DatagridSettingsPlugin.extend({
        /**
         * @inheritdoc
         */
        onBeforeToolbarInit: function(toolbarOptions) {
            const options = {
                datagrid: this.main,
                launcherOptions: _.extend(config, {
                    componentConstructor: toolbarOptions.componentConstructor || DatagridSettingView,
                    columns: this.main.columns,
                    collection: this.main.columns,
                    allowDialog: false
                }, toolbarOptions.datagridSettings),
                order: 600
            };

            toolbarOptions.addToolbarAction(new ShowComponentAction(options));
        }
    });

    return FrontendDatagridSettingsPlugin;
});

import _ from 'underscore';
import __ from 'orotranslation/js/translator';
import ShowComponentAction from 'oro/datagrid/action/show-component-action';
import DatagridSettingsPlugin from 'orodatagrid/js/app/plugins/grid/datagrid-settings-plugin';
import DatagridSettingView from 'orodatagrid/js/app/views/grid/datagrid-settings-view';
import moduleConfig from 'module-config';
const config = {
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
    },
    ...moduleConfig(module.id)
};

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

export default FrontendDatagridSettingsPlugin;

define(function(require) {
    'use strict';

    var ComponentShortcutsManager = require('oroui/js/component-shortcuts-manager');

    ComponentShortcutsManager.add('expand-text', {
        moduleName: 'oroui/js/app/components/jquery-widget-component',
        scalarOption: 'maxLength',
        options: {
            widgetModule: 'orofrontend/default/js/widgets/expand-text-widget'
        }
    });

    ComponentShortcutsManager.add('line-clamp', {
        moduleName: 'oroui/js/app/components/jquery-widget-component',
        scalarOption: 'lineClamp',
        options: {
            widgetModule: 'orofrontend/default/js/widgets/line-clamp-widget'
        }
    });

    ComponentShortcutsManager.add('elastic-area', {
        moduleName: 'oroui/js/app/components/jquery-widget-component',
        scalarOption: 'elasticArea',
        options: {
            widgetModule: 'orofrontend/default/js/widgets/elastic-area-widget'
        }
    });

    ComponentShortcutsManager.add('print-page', {
        moduleName: 'oroui/js/app/components/jquery-widget-component',
        scalarOption: 'printPage',
        options: {
            widgetModule: 'orofrontend/blank/js/widgets/print-page-widget'
        }
    });

    ComponentShortcutsManager.add('sticky', {
        moduleName: 'oroui/js/app/components/view-component',
        scalarOption: 'offsetSelector',
        options: {
            view: 'orofrontend/default/js/app/views/sticky-view'
        }
    });
});

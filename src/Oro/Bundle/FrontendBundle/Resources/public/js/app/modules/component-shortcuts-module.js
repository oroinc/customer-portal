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
});

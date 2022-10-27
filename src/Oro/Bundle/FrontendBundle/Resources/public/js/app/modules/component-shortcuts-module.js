import ComponentShortcutsManager from 'oroui/js/component-shortcuts-manager';

ComponentShortcutsManager.add('expand-text', {
    moduleName: 'oroui/js/app/components/jquery-widget-component',
    scalarOption: 'maxLength',
    options: {
        widgetModule: 'orofrontend/default/js/widgets/expand-text-widget'
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
        widgetModule: 'orofrontend/default/js/widgets/print-page-widget'
    }
});

ComponentShortcutsManager.add('sticky', {
    moduleName: 'oroui/js/app/components/view-component',
    scalarOption: 'offsetSelector',
    options: {
        view: 'orofrontend/default/js/app/views/sticky-view'
    }
});

ComponentShortcutsManager.add('proxy-focus', {
    moduleName: 'oroui/js/app/components/view-component',
    scalarOption: 'focusElementSelector',
    options: {
        view: 'orofrontend/default/js/app/views/proxy-focus-view'
    }
});

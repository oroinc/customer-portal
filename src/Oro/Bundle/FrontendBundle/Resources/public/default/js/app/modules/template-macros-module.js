import {macros} from 'underscore';

macros('orofrontend', {
    /**
     * Renders svg icon
     *
     * @param {Object} data
     * @param {Object|string} data.id
     * @param {Object|string} data.width optional
     * @param {Object|string} data.height optional
     * @param {Object|string} data.role optional
     * @param {Object|string} data.fill optional
     * @param {string?} data.ariaHidden optional
     */
    renderIcon: require('tpl-loader!orofrontend/default/templates/macros/svg-icon.html')
});

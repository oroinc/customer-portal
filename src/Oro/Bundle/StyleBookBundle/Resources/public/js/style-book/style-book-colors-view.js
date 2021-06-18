define(function(require) {
    'use strict';

    const template = require('tpl-loader!orostylebook/templates/style-book/style-book-colors-view.html');
    const BaseView = require('oroui/js/app/views/base/view');
    const _ = require('underscore');

    const StyleBookColorsView = BaseView.extend({
        autoRender: true,

        /**
         * @property
         */
        template: template,

        /**
         * @property
         */
        prefix: '--style-book-color',

        /**
         * @property
         */
        separator: '-',

        /**
         * @property
         */
        computedStyle: null,

        /**
         * @inheritdoc
         */
        constructor: function StyleBookColorsView(options) {
            this.computedStyle = getComputedStyle(document.documentElement);

            StyleBookColorsView.__super__.constructor.call(this, options);
        },

        /**
         * @inheritdoc
         * @returns {{colorPalette: {}}}
         */
        getTemplateData: function() {
            const colorPalette = {};
            let paletteIndex = 0;
            let paletteName = this._getProperty(['palette', paletteIndex]);

            while (paletteName.length) {
                let keyIndex = 0;
                colorPalette[paletteName] = {};

                while (this._getProperty([paletteName, keyIndex]).length) {
                    const key = this._getProperty([paletteName, keyIndex]);
                    colorPalette[paletteName][key] = this._getProperty([paletteName, key]);

                    keyIndex++;
                }

                paletteIndex++;
                paletteName = this._getProperty(['palette', paletteIndex]);
            }

            return {
                colorPalette: colorPalette
            };
        },

        /**
         * Get CSS property from concat name
         *
         * @param props
         * @returns {string}
         * @private
         */
        _getProperty: function(props) {
            if (!_.isArray(props)) {
                props = [props];
            }

            props.unshift(this.prefix);

            return this.computedStyle.getPropertyValue(props.join(this.separator)).trim();
        }
    });

    return StyleBookColorsView;
});

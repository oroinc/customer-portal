define(function(require) {
    'use strict';

    var StyleBookColorsView;
    var template = require('tpl-loader!orostylebook/templates/style-book/style-book-colors-view.html');
    var BaseView = require('oroui/js/app/views/base/view');
    var _ = require('underscore');

    StyleBookColorsView = BaseView.extend({
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
         * @inheritDoc
         */
        constructor: function StyleBookColorsView() {
            this.computedStyle = getComputedStyle(document.documentElement);

            StyleBookColorsView.__super__.constructor.apply(this, arguments);
        },

        /**
         * @inheritDoc
         * @returns {{colorPalette: {}}}
         */
        getTemplateData: function() {
            var colorPalette = {};
            var paletteIndex = 0;
            var paletteName = this._getProperty(['palette', paletteIndex]);

            while (paletteName.length) {
                var keyIndex = 0;
                colorPalette[paletteName] = {};

                while (this._getProperty([paletteName, keyIndex]).length) {
                    var key = this._getProperty([paletteName, keyIndex]);
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

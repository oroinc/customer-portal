import template from 'tpl-loader!orostylebook/templates/style-book/style-book-colors-view.html';
import BaseView from 'oroui/js/app/views/base/view';
import _ from 'underscore';

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
            const transPaletteKey = `oro_stylebook.color_pallet.${paletteName}`;
            const transPaletteResult = _.__(transPaletteKey);
            let keyIndex = 0;
            colorPalette[paletteName] = {};
            colorPalette[paletteName]['colors'] = {};
            colorPalette[paletteName]['description'] = transPaletteKey !== transPaletteResult
                ? transPaletteResult : null;


            while (this._getProperty([paletteName, keyIndex]).length) {
                const key = this._getProperty([paletteName, keyIndex]);
                const transColorPaletteKey = `oro_stylebook.color_pallet.${paletteName}-${key}`;
                const transColorPaletteResult = _.__(transColorPaletteKey);

                colorPalette[paletteName]['colors'] [key] = {
                    key,
                    color: this._getProperty([paletteName, key]),
                    description: transColorPaletteKey !== transColorPaletteResult
                        ? transColorPaletteResult : null
                };

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
        if (!Array.isArray(props)) {
            props = [props];
        }

        props.unshift(this.prefix);

        return this.computedStyle.getPropertyValue(props.join(this.separator)).trim();
    }
});

export default StyleBookColorsView;

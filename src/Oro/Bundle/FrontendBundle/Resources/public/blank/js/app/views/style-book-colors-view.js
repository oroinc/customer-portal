define(function(require) {
    'use strict';

    var StyleBookColorsView;
    var template = require('tpl!orofrontend/templates/style-book/style-book-colors-view.html');
    var BaseView = require('oroui/js/app/views/base/view');
    require('prismjs');
    require('prismjs-scss');

    StyleBookColorsView = BaseView.extend({
        autoRender: true,

        /**
         * @property
         */
        optionNames: BaseView.prototype.optionNames.concat([
            
        ]),

        /**
         * @property
         */
        template: template,

        /**
         * @property
         */
        prefix: '--style-book-color',

        /**
         * @inheritDoc
         */
        constructor: function StyleBookColorsView() {
            StyleBookColorsView.__super__.constructor.apply(this, arguments);
        },

        /**
         * @inheritDoc
         */
        initialize: function(options) {

            StyleBookColorsView.__super__.initialize.apply(this, arguments);
        },

        getTemplateData: function() {
            var computedStyle = getComputedStyle(document.documentElement);
            var colorPalette = {};
            var paletteIndex = 0;

            while(computedStyle.getPropertyValue(this.prefix + '-palette-' + paletteIndex).length) {
                var keyIndex = 0;
                var paletteName = computedStyle.getPropertyValue(this.prefix + '-palette-' + paletteIndex);
                colorPalette[paletteName] = {};

                while(computedStyle.getPropertyValue(this.prefix + '-' + paletteName + '-' + keyIndex).length) {
                    var key = computedStyle.getPropertyValue(this.prefix + '-' + paletteName + '-' + keyIndex);
                    var color = computedStyle.getPropertyValue(this.prefix + '-' + paletteName + '-' + key);

                    colorPalette[paletteName][key] = color;

                    keyIndex++;
                }

                paletteIndex++;
            }

            return {
                colorPalette: colorPalette
            };
        },

        render: function() {
            StyleBookColorsView.__super__.render.apply(this, arguments);

            this.$el.find('code[class*="language-"]').each(function() {
                Prism.highlightElement(this, true);
            });
        }
    });

    return StyleBookColorsView;
});

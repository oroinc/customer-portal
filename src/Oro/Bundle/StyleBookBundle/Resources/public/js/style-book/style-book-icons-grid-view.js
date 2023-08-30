import $ from 'jquery';
import BaseView from 'oroui/js/app/views/base/view';

import template from 'tpl-loader!orostylebook/templates/style-book/style-book-icons.html';

const StyleBookIconsGridView = BaseView.extend({
    /**
     * @inheritdoc
     */
    optionNames: BaseView.prototype.optionNames.concat([
        'svgUrl'
    ]),

    /**
     * @inheritdoc
     */
    autoRender: false,

    /**
     * @inheritdoc
     */
    template,


    constructor: function StyleBookIconsGridView(options) {
        StyleBookIconsGridView.__super__.constructor.call(this, options);
    },

    /**
     * @inheritdoc
     */
    initialize(options) {
        if (!options.svgUrl) {
            throw new Error('Option "svgUrl" is required');
        }

        $.ajax({
            url: options.svgUrl,
            type: 'GET',
            success: (sentData, status, response) => {
                const svg = new DOMParser().parseFromString(
                    response.responseText.trim(),
                    'image/svg+xml'
                );

                const iconsData = [...svg.querySelectorAll('symbol')].map(symbol => {
                    return {
                        id: symbol.id
                    };
                });

                const template = this.getTemplateFunction();
                const html = template({
                    svgUrl: options.svgUrl,
                    iconsData
                });
                this.$el.html(html);
            }
        });

        StyleBookIconsGridView.__super__.initialize.call(this, options);
    }
});

export default StyleBookIconsGridView;

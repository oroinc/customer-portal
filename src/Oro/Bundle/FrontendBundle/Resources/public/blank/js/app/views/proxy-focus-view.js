import $ from 'jquery';
import BaseView from 'oroui/js/app/views/base/view';

const ProxyFocusView = BaseView.extend({
    /**
     * @inheritDoc
     */
    events: {
        click: 'onClick'
    },

    /**
     * Selector of element to set focus on
     */
    focusElementSelector: null,

    /**
     * @inheritDoc
     */
    constructor: function ProxyFocusView(options) {
        ProxyFocusView.__super__.constructor.call(this, options);
    },

    /**
     * @inheritDoc
     */
    initialize(options) {
        if (!options.focusElementSelector) {
            throw new Error('option focusElementSelector is not defined');
        } else {
            this.focusElementSelector = options.focusElementSelector;
        }

        ProxyFocusView.__super__.initialize.call(this, options);
    },

    onClick() {
        const $focusElement = $(this.focusElementSelector);

        if ($focusElement.is(':tabbable')) {
            $focusElement.trigger('focus');
        } else {
            $focusElement
                .attr('tabindex', 0)
                .trigger('focus')
                .removeAttr('tabindex');
        }
    }
});

export default ProxyFocusView;

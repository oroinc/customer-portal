import $ from 'jquery';
import BaseView from 'oroui/js/app/views/base/view';
import 'jquery-ui/tabbable';

const ProxyFocusView = BaseView.extend({
    /**
     * @inheritdoc
     */
    events: {
        click: 'onClick'
    },

    /**
     * Selector of element to set focus on
     */
    focusElementSelector: null,

    /**
     * @inheritdoc
     */
    constructor: function ProxyFocusView(options) {
        ProxyFocusView.__super__.constructor.call(this, options);
    },

    /**
     * @inheritdoc
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

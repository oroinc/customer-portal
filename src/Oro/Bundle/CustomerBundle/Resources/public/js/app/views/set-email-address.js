import $ from 'jquery';
import _ from 'underscore';
import BaseView from 'oroui/js/app/views/base/view';

const SetEmailAddressView = BaseView.extend({
    options: {
        elementSelector: null,
        emailAddress: null
    },

    events: {
        click: 'clickHandler'
    },

    constructor: function SetEmailAddressView(options) {
        SetEmailAddressView.__super__.constructor.call(this, options);
    },

    initialize: function(options) {
        this.options = _.defaults(options || {}, this.options);

        SetEmailAddressView.__super__.initialize.call(this, options);
    },

    clickHandler: function(e) {
        e.preventDefault(this.options.emailAddress);

        const $elementToSet = $(this.options.elementSelector);

        if (!$elementToSet.length) {
            return;
        }

        $elementToSet.val(this.options.emailAddress);
        $elementToSet.trigger('change');
    }
});

export default SetEmailAddressView;

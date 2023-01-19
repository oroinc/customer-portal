define(function(require) {
    'use strict';

    const BaseView = require('oroui/js/app/views/base/view');
    const _ = require('underscore');
    const mediator = require('oroui/js/mediator');

    const StyleBookDomRelocation = BaseView.extend({
        events: {
            'click [data-relocation-trigger]': 'onMove'
        },

        moved: false,

        constructor: function StyleBookDomRelocation(options) {
            return StyleBookDomRelocation.__super__.constructor.call(this, options);
        },

        initialize: function(options) {
            this.options = _.omit(options, ['el']);
            StyleBookDomRelocation.__super__.initialize.call(this, options);
        },

        onMove: function() {
            const $element = this.$el.find('[data-relocate]');
            $element.removeData().attr('data-dom-relocation-options', JSON.stringify({
                responsive: [
                    {
                        viewport: 'desktop',
                        moveTo: this.moved ? '.dom-relocation-point' : '.dom-relocation-target',
                        prepend: !this.moved ? this.options.prepend : false,
                        sibling: !this.moved ? this.options.sibling : '.sibling-1',
                        endpointClass: !this.moved ? this.options.endpointClass : null
                    }
                ]
            }));

            this.moved = !this.moved;

            if (!this.moved) {
                $element.removeClass(this.options.endpointClass);
            }

            mediator.trigger('layout:reposition');
        }
    });

    return StyleBookDomRelocation;
});

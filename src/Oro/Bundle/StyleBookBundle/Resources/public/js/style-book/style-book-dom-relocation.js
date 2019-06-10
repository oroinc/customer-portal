define(function(require) {
    'use strict';

    var StyleBookDomRelocation;
    var BaseView = require('oroui/js/app/views/base/view');
    var _ = require('underscore');
    var mediator = require('oroui/js/mediator');

    StyleBookDomRelocation = BaseView.extend({
        events: {
            'click [data-relocation-trigger]': 'onMove'
        },

        moved: false,

        constructor: function StyleBookDomRelocation() {
            return StyleBookDomRelocation.__super__.constructor.apply(this, arguments);
        },

        initialize: function(options) {
            this.options = _.omit(options, ['el']);
            StyleBookDomRelocation.__super__.initialize.apply(this, arguments);
        },

        onMove: function() {
            var $element = this.$el.find('[data-relocate]');
            $element.removeData().attr('data-dom-relocation-options', JSON.stringify({
                responsive: [
                    {
                        viewport: {
                            maxScreenType: 'desktop'
                        },
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

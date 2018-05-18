define(function(require) {
    'use strict';

    var StyleBookDomRelocation;
    var BaseView = require('oroui/js/app/views/base/view');
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
            StyleBookDomRelocation.__super__.initialize.apply(this, arguments);
        },

        onMove: function() {
            this.$el.find('[data-relocate]').removeData().attr('data-dom-relocation-options', JSON.stringify({
                responsive: [
                    {
                        viewport: {
                            maxScreenType: 'desktop'
                        },
                        moveTo: this.moved ? '.dom-relocation-point' : '.dom-relocation-target'
                    }
                ]
            }));

            this.moved = !this.moved;

            mediator.trigger('layout:reposition');
        }
    });

    return StyleBookDomRelocation;
});

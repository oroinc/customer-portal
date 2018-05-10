define(function(require) {
    'use strict';

    var StyleBookStickyPanel;
    var BaseView = require('oroui/js/app/views/base/view');
    var mediator = require('oroui/js/mediator');

    StyleBookStickyPanel = BaseView.extend({
        events: {
            'click [data-toggle]': 'toggle'
        },

        moved: false,

        constructor: function StyleBookStickyPanel() {
            return StyleBookStickyPanel.__super__.constructor.apply(this, arguments);
        },

        initialize: function(options) {
            StyleBookStickyPanel.__super__.initialize.apply(this, arguments);
        },

        toggle: function() {
            if (!this.moved) {
                this.$('[data-move-to-sticky]')
                    .attr('data-sticky-target', 'top-sticky-panel')
                    .attr('data-sticky', JSON.stringify({
                        toggleClass: 'sticked',
                        placeholderId: 'style-book-sticky-header',
                        alwaysInSticky: true
                    }));
            } else {
                this.$el.find('[data-move-to-sticky]')
                    .removeAttr('data-sticky-target')
                    .removeAttr('data-sticky');
            }

            this.moved = !this.moved;

            this.$('[data-toggle]').text(this.moved ? 'Off sticky' : 'On sticky');

            mediator.trigger('page:afterChange');
        }
    });

    return StyleBookStickyPanel;
});

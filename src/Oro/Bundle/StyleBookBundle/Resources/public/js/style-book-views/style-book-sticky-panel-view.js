define(function(require) {
    'use strict';

    var StyleBookStickyPanel;
    var BaseView = require('oroui/js/app/views/base/view');
    var mediator = require('oroui/js/mediator');
    var _ = require('underscore');

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

                this.$('[data-move-to-sticky]').find('[data-toggle]').on('click', _.bind(this.stickyOff, this));
            } else {
                this.$el.find('[data-move-to-sticky]')
                    .removeAttr('data-sticky-target')
                    .removeAttr('data-sticky');
            }

            this.moved = !this.moved;

            this.$('[data-toggle]').text(
                this.moved
                    ? _.__('oro_stylebook.groups.jscomponent.sticky_panel_view.button.sticky_on')
                    : _.__('oro_stylebook.groups.jscomponent.sticky_panel_view.button.sticky_off')
            );

            mediator.trigger('page:afterChange');
        }
    });

    return StyleBookStickyPanel;
});

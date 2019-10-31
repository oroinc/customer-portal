define(function(require) {
    'use strict';

    const BaseView = require('oroui/js/app/views/base/view');
    const mediator = require('oroui/js/mediator');
    const _ = require('underscore');

    const StyleBookStickyPanel = BaseView.extend({
        events: {
            'click [data-toggle]': 'toggle'
        },

        moved: false,

        constructor: function StyleBookStickyPanel(options) {
            return StyleBookStickyPanel.__super__.constructor.call(this, options);
        },

        initialize: function(options) {
            StyleBookStickyPanel.__super__.initialize.call(this, options);
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

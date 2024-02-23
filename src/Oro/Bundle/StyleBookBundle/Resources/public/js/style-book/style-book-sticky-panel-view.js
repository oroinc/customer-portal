define(function(require) {
    'use strict';

    const BaseView = require('oroui/js/app/views/base/view');
    const _ = require('underscore');

    const StyleBookStickyPanel = BaseView.extend({
        events: {
            'click [data-bottom]': 'setBottom'
        },

        constructor: function StyleBookStickyPanel(options) {
            return StyleBookStickyPanel.__super__.constructor.call(this, options);
        },

        setBottom() {
            const $box = this.$('.sticky-panel-box');

            if ($box.hasClass('sticky--top')) {
                $box.removeClass('sticky--top');
                $box.addClass('sticky--bottom');

                this.$('[data-bottom]').text(_.__('oro_stylebook.groups.jscomponent.sticky_panel_view.set_bottom.on'));
            } else {
                $box.removeClass('sticky--bottom');
                $box.addClass('sticky--top');

                this.$('[data-bottom]').text(_.__('oro_stylebook.groups.jscomponent.sticky_panel_view.set_bottom.off'));
            }
        }
    });

    return StyleBookStickyPanel;
});

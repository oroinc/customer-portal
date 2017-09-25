define(function(require) {
    'use strict';

    var CounterBadgeView;
    var BaseView = require('oroui/js/app/views/base/view');

    CounterBadgeView = BaseView.extend({
        /**
         * @property
         */
        optionNames: BaseView.prototype.optionNames.concat([
            'tagName', 'className', 'count'
        ]),

        /**
         * @property
         */
        tagName: 'span',

        /**
         * @property
         */
        className: 'badge badge--info badge--xs badge--offset-none',
        
        /**
         * @property
         */
        count: 0,
        
        /**
         * @inheritDoc
         */
        initialize: function(options) {
            this.setCount(this.count);

            CounterBadgeView.__super__.initialize.apply(this, arguments);
        },

        /**
         * Count setter
         */
        setCount: function(count) {
            this.$el
                .html(count || '')
                .toggleClass('hidden', !count);
        }
    });

    return CounterBadgeView;
});

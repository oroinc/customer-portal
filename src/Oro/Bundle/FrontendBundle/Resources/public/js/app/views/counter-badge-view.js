define(function(require) {
    'use strict';

    var CounterBadgeView;
    var BaseView = require('oroui/js/app/views/base/view');

    CounterBadgeView = BaseView.extend({
        /**
         * @property
         */
        optionNames: BaseView.prototype.optionNames.concat([
            'count'
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
        constructor: function CounterBadgeView() {
            CounterBadgeView.__super__.constructor.apply(this, arguments);
        },

        /**
         * @inheritDoc
         */
        initialize: function(options) {
            this.setCount(this.count);

            CounterBadgeView.__super__.initialize.apply(this, arguments);
        },

        /**
         * Count setter
         * @param {String | Number} count
         */
        setCount: function(count) {
            this.$el
                .html(count || '')
                .toggleClass('hidden', !count);
        }
    });

    return CounterBadgeView;
});

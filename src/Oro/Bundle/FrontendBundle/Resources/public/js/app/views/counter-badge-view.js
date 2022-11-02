define(function(require) {
    'use strict';

    const BaseView = require('oroui/js/app/views/base/view');

    const CounterBadgeView = BaseView.extend({
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
         * @inheritdoc
         */
        constructor: function CounterBadgeView(options) {
            CounterBadgeView.__super__.constructor.call(this, options);
        },

        /**
         * @inheritdoc
         */
        initialize: function(options) {
            this.setCount(this.count);

            CounterBadgeView.__super__.initialize.call(this, options);
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

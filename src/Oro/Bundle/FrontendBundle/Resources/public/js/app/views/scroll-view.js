define(function(require) {
    'use strict';

    const BaseView = require('oroui/js/app/views/base/view');
    require('jquery.mousewheel');

    const ScrollView = BaseView.extend({
        /**
         * @property {jQuery}
         */
        $scrollableContainer: null,

        /**
         * @property {jQuery}
         */
        $followXAxis: null,

        /**
         * @property {jQuery}
         */
        $followYAxis: null,

        /**
         * @inheritdoc
         */
        constructor: function ScrollView(options) {
            ScrollView.__super__.constructor.call(this, options);
        },

        /**
         * Initialize
         *
         * @param {Object} options
         */
        initialize: function(options) {
            ScrollView.__super__.initialize.apply(this, options);

            this.$scrollableContainer = this.find('[data-scroll-view]');
            this.$followXAxis = this.find('[data-scroll-view-follow="x"]');
            this.$followYAxis = this.find('[data-scroll-view-follow="y"]');

            this.initScrollContainer();
            this.setScrollStatus();
        },

        /**
         * Init scroll container
         */
        initScrollContainer: function() {
            this.setStartPosition();

            this.$scrollableContainer.on('scroll mousewheel', e => {
                e.stopPropagation();
                this.updateFollowersPosition(e.currentTarget);
                this.setScrollStatus();
            });
        },

        updateFollowersPosition: function(element) {
            if (element) {
                this._transformFollowers('x', -element.scrollLeft);
            }
            if (element) {
                this._transformFollowers('y', -element.scrollTop);
            }
        },

        setStartPosition: function() {
            this.updateFollowersPosition(this.$scrollableContainer.get(0));
        },

        setScrollStatus: function() {
            const scroll = this.hasScroll(this.$scrollableContainer.get(0));

            this.$el
                .toggleClass('has-x-scroll', scroll.x)
                .toggleClass('has-y-scroll', scroll.y);
        },

        hasScroll: function(element) {
            return {
                x: element.scrollWidth > element.clientWidth,
                y: element.scrollHeight > element.clientHeight
            };
        },

        _transformFollowers: function(direction, value) {
            this['$follow' + direction.toUpperCase() + 'Axis'].css({
                transform: 'translate' + direction.toUpperCase() + '(' + value + 'px)'
            });
        },

        dispose: function() {
            if (this.disposed) {
                return;
            }

            this.$scrollableContainer.off();

            delete this.$scrollableContainer;
            delete this.$followXAxis;
            delete this.$followYAxis;

            ScrollView.__super__.dispose.call(this);
        }
    });

    return ScrollView;
});

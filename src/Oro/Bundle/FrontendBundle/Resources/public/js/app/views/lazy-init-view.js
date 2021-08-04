define(function(require) {
    'use strict';

    const $ = require('jquery');
    const _ = require('underscore');
    const BaseView = require('oroui/js/app/views/base/view');

    /**
     * @class LazyInitView
     * @extends BaseView
     */
    const LazyInitView = BaseView.extend({
        constructor: function LazyInitView(options) {
            LazyInitView.__super__.constructor.call(this, options);
        },

        /**
         * @inheritdoc
         */
        optionNames: BaseView.prototype.optionNames.concat(['lazy']),

        /**
         * @property {Object}
         */
        options: null,

        /**
         * On init dispatcher
         * @property String
         */
        lazy: 'scroll',

        /**
         * @property {jQuery}
         */
        $window: null,

        listen: {
            'page:afterChange mediator': '_onPageAfterChange'
        },

        /**
         * Initialize
         *
         * @param {Object} options
         */
        initialize: function(options) {
            this.options = options;

            if (this.lazy === 'scroll') {
                this.$window = $(window);
                this.$window.on(`scroll${this.eventNamespace()}`, this._onScrollDemand.bind(this));
                this._onScrollDemand();
            }

            return LazyInitView.__super__.initialize.call(this, options);
        },

        initLazyView: function() {
            const layoutOptions = _.omit(this.options, 'el', 'name');
            return this.initLayout(layoutOptions);
        },

        _onScrollDemand: function() {
            if (this.$el.offset().top < (window.pageYOffset + window.innerHeight / 0.5)) {
                this.initLazyView();
                this.$window.off(`scroll${this.eventNamespace()}`);
            }
        },

        _onPageAfterChange: function() {
            switch (this.lazy) {
                case 'scroll':
                    this._onScrollDemand();
                    break;
                default:
                    this.initLazyView();
            }
        },

        dispose: function() {
            if (this.disposed) {
                return;
            }

            this.$window.off(`scroll${this.eventNamespace()}`);
            delete this.$window;

            LazyInitView.__super__.dispose.call(this);
        }
    });

    return LazyInitView;
});

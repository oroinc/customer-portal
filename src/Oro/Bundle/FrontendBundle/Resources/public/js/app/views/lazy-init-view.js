define(function(require) {
    'use strict';

    var LazyInitView;
    var $ = require('jquery');
    var _ = require('underscore');
    var BaseView = require('oroui/js/app/views/base/view');
    var mediator = require('oroui/js/mediator');

    /**
     * @class LazyInitView
     * @extends BaseView
     */
    LazyInitView = BaseView.extend({
        constructor: function LazyInitView() {
            LazyInitView.__super__.constructor.apply(this, arguments);
        },

        /**
         * @inheritDoc
         */
        optionNames: BaseView.prototype.optionNames.concat(['lazy']),

        /**
         * @property {Object}
         */
        options: null,

        /**
         * Can be "scroll", "page-init"
         * @property String
         */
        lazy: 'scroll',

        /**
         * @property {jQuery}
         */
        $window: null,

        /**
         * @inheritDoc
         */
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

            if (!this.lazy) {
                this._onPageAfterChange();
            }

            if (this.lazy === 'scroll') {
                this.$window = $(window);
                this.$window.on('scroll' + this.eventNamespace(), _.bind(this._onScrollDemand, this));
                this._onScrollDemand();
            }

            if (this.lazy === 'page-init') {
                mediator.on('page:afterChange', this._onPageAfterChange, this);
            }

            return LazyInitView.__super__.initialize.apply(this, arguments);
        },

        initLazyView: function() {
            return this.initLayout(this.options);
        },

        _onScrollDemand: function() {
            if (this.$el.offset().top < (window.scrollY + window.innerHeight / 0.5)) {
                this.initLazyView();
                this.$window.off('scroll' + this.eventNamespace());
            }
        },

        _onPageAfterChange: function() {
            if (this.lazy === 'scroll') {
                this._onScrollDemand();
            }
            if (this.lazy === 'page-init') {
                this.initLazyView();
            }
        },

        _unbindEvents: function() {
            this.$window.off('scroll' + this.eventNamespace());
            this.stopListening();
            mediator.off(null, null, this);
        },

        dispose: function() {
            if (this.disposed) {
                return;
            }

            this._unbindEvents();
            delete this.$window;
            delete this.options;
            delete this.$el;
        }
    });

    return LazyInitView;
});

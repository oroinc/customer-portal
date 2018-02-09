define(function(require) {
    'use strict';

    var LazyInirializeView;
    var $ = require('jquery');
    var _ = require('underscore');
    var BaseView = require('oroui/js/app/views/base/view');
    var mediator = require('oroui/js/mediator');

    LazyInirializeView = BaseView.extend({
        optionNames: BaseView.prototype.optionNames.concat(['lazy']),

        options: null,
        /**
         * Can be "scroll", "page-init"
         * @property String
         */
        lazy: 'scroll',

        $window: null,

        listen: {
            'page:afterChange mediator': '_onPageAfterChange'
        },

        initialize: function(options) {
            this.options = options;

            if (!this.lazy) {
                this._onPageAfterChange();
            }

            if (this.lazy === 'scroll') {
                this.$window = $(window);
                this.$window.on('scroll' + this.eventNamespace(), _.bind(this._onScrollDemand, this));
                this._onScrollDemand();
                this.listenToOnce(this.collection, {
                    'reset': this.dispose,
                    'backgrid:selectAllVisible': this.initLazyView
                });
            }

            if (this.lazy === 'page-init') {
                mediator.on('page:afterChange', this._onPageAfterChange, this);
            }

            return LazyInirializeView.__super__.initialize.apply(this, arguments);
        },

        initLazyView: function() {
            this.initLayout(this.options);
        },

        _onScrollDemand: function() {
            if (this.$el.offset().top / 2 < (window.scrollY + window.innerHeight)) {
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
        },

        dispose: function() {
            this._unbindEvents();
            delete this.options;
            delete this.$el;
        }
    });

    return LazyInirializeView;
});

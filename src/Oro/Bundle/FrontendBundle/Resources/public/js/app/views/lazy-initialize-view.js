define(function(require) {
    'use strict';

    var LazyInirializeView;
    var $ = require('jquery');
    var BaseView = require('oroui/js/app/views/base/view');
    var mediator = require('oroui/js/mediator');

    LazyInirializeView = BaseView.extend({
        optionNames: BaseView.prototype.optionNames.concat(['lazy']),

        /**
         * Can by "scroll", "page-init"
         * @property String
         */
        lazy: 'scroll',

        $window: null,

        listen: {
            'page:afterChange mediator': '_onPageAfterChange'
        },

        initialize: function(options) {
            if (!this.lazy) {
                this._onPageAfterChange();
            }

            if (this.lazy === 'scroll') {
                this.$window = $(window);
                this.$window.on('scroll' + this.eventNamespace(), _.bind(this._onScrollDemand, this));
            }

            if (this.lazy === 'page-init') {
                mediator.on('page:afterChange', this._onPageAfterChange, this);
            }

            return LazyInirializeView.__super__.initialize.apply(this, arguments);
        },

        _onScrollDemand: function(event) {
            if (this.$el.offset().top < (window.scrollY + window.innerHeight)) {
                this.initLayout();
                this.$window.off('scroll' + this.eventNamespace());
            }
        },

        _onPageAfterChange: function() {
            if (this.lazy === 'scroll') {
                this._onScrollDemand();
            }
            if (this.lazy === 'page-init') {
                this.initLayout();
            }
        }
    });

    return LazyInirializeView;
});

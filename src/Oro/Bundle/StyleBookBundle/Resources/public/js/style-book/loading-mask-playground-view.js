define(function(require) {
    'use strict';

    var LoadingMaskPlaygroundView;
    var BaseView = require('oroui/js/app/views/base/view');
    var LoadingMaskView = require('oroui/js/app/views/loading-mask-view');
    var mediator = require('oroui/js/mediator');
    var _ = require('underscore');

    LoadingMaskPlaygroundView = BaseView.extend({
        optionNames: BaseView.prototype.optionNames.concat(['el', 'loadingContainerSelector']),

        loadingContainerSelector: '[data-loading-container]',

        events: {
            'click [data-toggle]': 'toggle',
            'click [data-toggle-full-page]': 'toggleFullPage'
        },

        state: false,

        executeTimeout: 3000,

        options: {},

        constructor: function LoadingMaskPlaygroundView() {
            return LoadingMaskPlaygroundView.__super__.constructor.apply(this, arguments);
        },

        initialize: function(options) {
            LoadingMaskPlaygroundView.__super__.initialize.apply(this, arguments);

            this.options = _.extend({}, _.omit(options, this.optionNames), {
                container: this.$(this.loadingContainerSelector)
            });

            this.subview('loadingMask', new LoadingMaskView(this.options));
            this.subview('loadingMask').show();
            this.state = true;
        },

        toggle: function() {
            if (this.state) {
                this.subview('loadingMask').hide();
            } else {
                this.subview('loadingMask').show();
            }

            this.state = !this.state;
        },

        toggleFullPage: function() {
            mediator.execute('showLoading');

            _.delay(function() {
                mediator.execute('hideLoading');
            }, this.executeTimeout);
        }
    });

    return LoadingMaskPlaygroundView;
});

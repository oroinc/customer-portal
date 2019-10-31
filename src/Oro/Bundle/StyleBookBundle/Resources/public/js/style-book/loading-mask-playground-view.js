define(function(require) {
    'use strict';

    const BaseView = require('oroui/js/app/views/base/view');
    const LoadingMaskView = require('oroui/js/app/views/loading-mask-view');
    const mediator = require('oroui/js/mediator');
    const _ = require('underscore');

    const LoadingMaskPlaygroundView = BaseView.extend({
        optionNames: BaseView.prototype.optionNames.concat(['el', 'loadingContainerSelector']),

        loadingContainerSelector: '[data-loading-container]',

        events: {
            'click [data-toggle]': 'toggle',
            'click [data-toggle-full-page]': 'toggleFullPage'
        },

        state: false,

        executeTimeout: 3000,

        options: {},

        constructor: function LoadingMaskPlaygroundView(options) {
            return LoadingMaskPlaygroundView.__super__.constructor.call(this, options);
        },

        initialize: function(options) {
            LoadingMaskPlaygroundView.__super__.initialize.call(this, options);

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

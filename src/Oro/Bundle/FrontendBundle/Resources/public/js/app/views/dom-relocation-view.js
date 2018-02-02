define(function(require) {
    'use strict';

    var DomRelocationView;
    var viewportManager = require('oroui/js/viewport-manager');
    var BaseView = require('oroui/js/app/views/base/view');
    var _ = require('underscore');
    var $ = require('jquery');
    var module = require('module');

    var config = module.config();
    config = _.extend({
        resizeTimeout: 250,
        layoutTimeout: 250
    }, config);

    DomRelocationView = BaseView.extend({
        autoRender: true,

        optionNames: BaseView.prototype.optionNames.concat(['resizeTimeout', 'layoutTimeout']),

        resizeTimeout: config.resizeTimeout,

        layoutTimeout: config.layoutTimeout,

        listen: {
            'viewport:change mediator': 'onViewportChange',
            'layout:reposition mediator': 'onContentChange'
        },

        $elements: null,

        /**
         * @inheritDoc
         */
        initialize: function(options) {
            this.$window = $(window);
            this.onViewportChange = _.debounce(_.bind(this.onViewportChange, this), this.resizeTimeout);
            this.onContentChange = _.debounce(_.bind(this.onContentChange, this), this.layoutTimeout);

            return DomRelocationView.__super__.initialize.apply(this, arguments);
        },

        /**
         * @inheritDoc
         */
        render: function() {
            this.collectElements();
            this.moveElements();
            return this;
        },

        onContentChange: function() {
            this.render();
        },

        onViewportChange: function() {
            this.moveElements();
        },

        /**
         * @inheritDoc
         */
        dispose: function() {
            if (this.disposed) {
                return;
            }

            delete this.$elements;

            return DomRelocationView.__super__.dispose.apply(this, arguments);
        },

        moveElements: function() {
            if (!this.$elements.length) {
                return;
            }
            var viewport = viewportManager.getViewport();
            _.each(this.$elements, function(el) {
                var $el = $(el);
                var options = $el.data('dom-relocation-options');
                var targetOptions = this.checkTargetOptions(viewport, options.responsive);

                if (_.isObject(targetOptions)) {
                    if (!_.isEqual(options.targetViewport, targetOptions.viewport)) {
                        $(targetOptions.moveTo).first().append($el);
                        options.targetViewport = targetOptions.viewport;
                        options._moved = true;
                    }
                } else if (options._moved) {
                    options.$originalPosition.append($el);
                    options.targetViewport = null;
                    options._moved = false;
                }
            }, this);
        },

        checkTargetOptions: function(viewport, responsiveOptions) {
            for (var i = responsiveOptions.length - 1; i >= 0; i--) {
                if (viewport.isApplicable(responsiveOptions[i].viewport)) {
                    return responsiveOptions[i];
                }
            }
        },

        collectElements: function() {
            // data-dom-relocation deprecated, keep fo BC
            this.$elements = $('[data-dom-relocation], [data-dom-relocation-options]');
            _.each(this.$elements, function(el) {
                var $el = $(el);
                var options = $el.data('dom-relocation-options');
                if (options._loaded) {
                    return;
                }

                options._loaded = true;
                options.$originalPosition = $el.parent();
                options.responsive = options.responsive || [];
                options.targetViewport = null;
            }, this);
        }
    });

    return DomRelocationView;
});

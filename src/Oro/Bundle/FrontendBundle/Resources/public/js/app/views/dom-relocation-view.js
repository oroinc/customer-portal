define(function(require, exports, module) {
    'use strict';

    const viewportManager = require('oroui/js/viewport-manager');
    const BaseView = require('oroui/js/app/views/base/view');
    const _ = require('underscore');
    const $ = require('jquery');
    let config = require('module-config').default(module.id);

    config = _.extend({
        resizeTimeout: 250,
        layoutTimeout: 250
    }, config);

    /**
     * @DomRelocationView
     *
     *
     */
    const DomRelocationView = BaseView.extend({
        autoRender: true,

        optionNames: BaseView.prototype.optionNames.concat([
            'resizeTimeout', 'layoutTimeout'
        ]),

        resizeTimeout: config.resizeTimeout,

        layoutTimeout: config.layoutTimeout,

        listen: {
            'viewport:change mediator': 'onViewportChange',
            'layout:reposition mediator': 'onContentChange'
        },

        $elements: null,

        defaultOptions: {
            sibling: null,
            moveTo: null,
            endpointClass: 'relocated',
            prepend: false,
            responsive: [],
            targetViewport: null,
            _moved: false,
            _addedClass: null
        },

        /**
         * @inheritDoc
         */
        constructor: function DomRelocationView(options) {
            DomRelocationView.__super__.constructor.call(this, options);
        },

        /**
         * @inheritDoc
         */
        initialize: function(options) {
            this.$window = $(window);
            this.onViewportChange = _.debounce(this.onViewportChange.bind(this), this.resizeTimeout);
            this.onContentChange = _.debounce(this.onContentChange.bind(this), this.layoutTimeout);

            return DomRelocationView.__super__.initialize.call(this, options);
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

            return DomRelocationView.__super__.dispose.call(this);
        },

        /**
         * Move element action
         */
        moveElements: function() {
            if (!this.$elements.length) {
                return;
            }
            const viewport = viewportManager.getViewport();
            _.each(this.$elements, function(el) {
                const $el = $(el);
                const options = $el.data('dom-relocation-options');
                const targetOptions = this.checkTargetOptions(viewport, options.responsive);

                if (_.isObject(targetOptions)) {
                    if (!_.isEqual(options.targetViewport, targetOptions.viewport)) {
                        this.moveToTarget($el, targetOptions);
                    }
                } else if (options._moved) {
                    this.returnByIndex($el);
                }
            }, this);
        },

        /**
         * Checking applicable relocation from viewport state
         * @param {Object} viewport
         * @param {Array} responsiveOptions
         * @returns {Object}
         */
        checkTargetOptions: function(viewport, responsiveOptions) {
            for (let i = responsiveOptions.length - 1; i >= 0; i--) {
                if (viewport.isApplicable(responsiveOptions[i].viewport)) {
                    return responsiveOptions[i];
                }
            }
        },

        /**
         * Return element to original position
         * @param {jQuery.Element} $el
         */
        returnByIndex: function($el) {
            const options = $el.data('dom-relocation-options');

            if (options.originalOrder === 0) {
                options.$originalPosition.prepend($el);
            } else {
                options.$originalPosition.children().eq(options.originalOrder - 1).after($el);
            }

            if (options._addedClass) {
                $el.removeClass(options._addedClass);
            }

            options.targetViewport = null;
            options._moved = false;
        },

        /**
         * Move element to target parent
         * @param $el
         * @param targetOptions
         */
        moveToTarget: function($el, targetOptions) {
            const options = $el.data('dom-relocation-options');
            let $target = $(targetOptions.moveTo).first();

            if (targetOptions.sibling) {
                $target = $target.find(targetOptions.sibling).first();
                targetOptions.prepend
                    ? $target.before($el)
                    : $target.after($el);
            } else {
                targetOptions.prepend
                    ? $target.prepend($el)
                    : $target.append($el);
            }

            if (options._addedClass) {
                $el.removeClass(options._addedClass);
            }

            if (targetOptions.endpointClass) {
                $el.addClass(targetOptions.endpointClass);
                options._addedClass = targetOptions.endpointClass;
            }

            options.targetViewport = targetOptions.viewport;
            options._moved = true;
        },

        /**
         * Init and collect all element on page with relocation rules
         */
        collectElements: function() {
            // data-dom-relocation deprecated, keep fo BC
            this.$elements = $('[data-dom-relocation], [data-dom-relocation-options]');
            _.each(this.$elements, function(el) {
                const $el = $(el);
                const options = $el.data('dom-relocation-options');
                if (options._loaded) {
                    return;
                }
                _.extend(
                    _.defaults(options, this.defaultOptions),
                    {
                        $originalPosition: $el.parent(),
                        originalOrder: $el.index(),
                        _loaded: true
                    }
                );
            }, this);
        }
    });

    return DomRelocationView;
});

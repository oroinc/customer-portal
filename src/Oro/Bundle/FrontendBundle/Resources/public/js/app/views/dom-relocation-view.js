define(function(require, exports, module) {
    'use strict';

    const viewportManager = require('oroui/js/viewport-manager').default;
    const BaseView = require('oroui/js/app/views/base/view');
    const _ = require('underscore');
    const $ = require('jquery');
    const mediator = require('oroui/js/mediator');
    let config = require('module-config').default(module.id);

    config = _.extend({
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
            'layoutTimeout'
        ]),

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
         * @inheritdoc
         */
        constructor: function DomRelocationView(options) {
            DomRelocationView.__super__.constructor.call(this, options);
        },

        /**
         * @inheritdoc
         */
        initialize: function(options) {
            this.$window = $(window);
            this.onContentChange = _.debounce(this.onContentChange.bind(this), this.layoutTimeout);

            return DomRelocationView.__super__.initialize.call(this, options);
        },

        /**
         * @inheritdoc
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
         * @inheritdoc
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
            _.each(this.$elements, function(el) {
                const $el = $(el);
                const options = $el.data('dom-relocation-options');
                const targetOptions = this.checkTargetOptions(viewportManager, options.responsive);

                if (_.isObject(targetOptions)) {
                    if (!_.isEqual(options.targetViewport, targetOptions.viewport)) {
                        this.moveToTarget($el, targetOptions);
                    }
                } else if (options._moved) {
                    this.returnByIndex($el);
                }

                mediator.trigger('layout:content-relocated', $el);
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
            this.$elements = $('[data-dom-relocation-options]');
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

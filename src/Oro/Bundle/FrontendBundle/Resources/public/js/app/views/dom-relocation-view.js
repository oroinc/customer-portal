import viewportManager from 'oroui/js/viewport-manager';
import BaseView from 'oroui/js/app/views/base/view';
import _ from 'underscore';
import $ from 'jquery';
import mediator from 'oroui/js/mediator';
import moduleConfig from 'module-config';
const config = {
    layoutTimeout: 250,
    ...moduleConfig(module.id)
};

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
        startPointClass: '',
        prepend: false,
        responsive: [],
        scroll: [],
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
        this.onScrollChange = _.debounce(this.onScrollChange.bind(this), 150);

        return DomRelocationView.__super__.initialize.call(this, options);
    },

    /**
     * @inheritdoc
     */
    render: function() {
        this.collectElements();
        this.moveElements();
        this._moveElementsOnScroll();
        return this;
    },

    /**
     * @inheritDoc
     */
    delegateEvents(events) {
        DomRelocationView.__super__.delegateEvents.call(this, events);

        $(document).on(`scroll${this.eventNamespace()}`, this.onScrollChange.bind(this));
    },

    /**
     * @inheritDoc
     */
    undelegateEvents() {
        $(document).off(this.eventNamespace());
        return DomRelocationView.__super__.undelegateEvents.call(this);
    },

    onContentChange: function() {
        this.render();
    },

    onViewportChange: function() {
        this.moveElements();
    },

    onScrollChange() {
        this._moveElementsOnScroll();
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

        const $elementToMove = this.$elements.filter((i, el) => {
            return this.isElementForMovedOnResize(el);
        });

        _.each($elementToMove, el => {
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

            mediator.trigger('layout:content-relocated', $el, targetOptions);
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
        let placeholder = document.getElementById(options.placeholderId);

        if (!placeholder) {
            placeholder = [...options.$originalPosition.get(0).childNodes]
                .find(node => node.nodeType === Element.COMMENT_NODE && node.nodeValue === options.placeholderId);
        }

        if (placeholder) {
            $(placeholder).after($el);
            placeholder.remove();
        }

        if ($el.data('startPointClass')) {
            $el.addClass($el.data('startPointClass'));
            $el.removeData('startPointClass');
        }

        if (options._addedClass) {
            $el.removeClass(options._addedClass);
        }

        options.targetViewport = null;
        options._moved = false;
    },

    /**
     * @param {HTMLElement|jQuery.Element} el
     * @returns {HTMLElement}
     */
    createPlaceholderForElement(el) {
        const options = $(el).data('dom-relocation-options');
        const placeholder = document.createElement('div');

        placeholder.style.height = `${$(el).outerHeight(true)}px`;
        placeholder.id = options.placeholderId;

        return placeholder;
    },

    /**
     * @param {HTMLElement|jQuery.Element} el
     * @returns {Comment}
     */
    createCommentPlaceholderForElement(el) {
        const options = $(el).data('dom-relocation-options');

        return document.createComment(`${options.placeholderId}`);
    },

    /**
     * Move element to target parent
     * @param $el
     * @param targetOptions
     */
    moveToTarget: function($el, targetOptions) {
        const options = $el.data('dom-relocation-options') ?? {};

        let placeholder = this.createCommentPlaceholderForElement($el);

        if (this.isElementForMovedOnScroll($el)) {
            placeholder = this.createPlaceholderForElement($el);
        }

        $el.before(placeholder);

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

        if (targetOptions.startPointClass) {
            $el.removeClass(targetOptions.startPointClass);
            $el.data('startPointClass', targetOptions.startPointClass);
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

            if (options.responsive && options.scroll) {
                console.warn(`
                    %cLooks like you area using "responsive" and "scroll" simultaneously.
                    It may cause the conflicts during the relocating process.
                `, 'color:red');
            }

            _.extend(
                _.defaults(options, this.defaultOptions),
                {
                    $originalPosition: $el.parent(),
                    placeholderId: _.uniqueId('dom-relocation-placeholder-'),
                    originalOrder: $el.index(),
                    _loaded: true
                }
            );
        }, this);
    },

    /**
     * Check each element and move it based on scroll position
     */
    _moveElementsOnScroll() {
        if (!this.$elements.length) {
            return;
        }

        const $elementsToMove = this.$elements.filter((i, el) => {
            return this.isElementForMovedOnScroll(el);
        });

        $elementsToMove.each((i, el) => {
            const $el = $(el);
            const options = $el.data('dom-relocation-options');
            const targetOptions = this.checkTargetOptions(viewportManager, options.scroll);

            if (targetOptions === void 0 && options._moved) {
                this.returnByIndex($el);
            }

            const scrollTop = window.scrollY;

            if (options._moved) {
                const placeholder = document.getElementById(options.placeholderId);

                if (!placeholder) {
                    return;
                }

                const bottomThreshold = placeholder.getBoundingClientRect().y;

                if (scrollTop < bottomThreshold) {
                    options._moved = false;
                    this.returnByIndex($el);
                    mediator.trigger('layout:content-relocated', $el, options);
                }
            } else if (_.isObject(targetOptions)) {
                const bottomThreshold = el.getBoundingClientRect().bottom;
                if (!options._moved && scrollTop > bottomThreshold) {
                    options._moved = true;
                    this.moveToTarget($el, targetOptions);
                    mediator.trigger('layout:content-relocated', $el, options);
                }
            }
        });
    },

    /**
     * @param {HTMLElement|jQuery.Element} el
     * @returns {boolean}
     */
    isElementForMovedOnResize(el) {
        return $(el).data('dom-relocation-options').responsive?.length;
    },

    /**
     * @param {HTMLElement|jQuery.Element} el
     * @returns {boolean}
     */
    isElementForMovedOnScroll(el) {
        return $(el).data('dom-relocation-options').scroll?.length;
    }
});

export default DomRelocationView;

define(function(require) {
    'use strict';

    var StyleBookElementsNavigationView;
    var BaseView = require('oroui/js/app/views/base/view');
    var $ = require('jquery');
    var _ = require('underscore');

    StyleBookElementsNavigationView = BaseView.extend({
        /**
         * @property {String}
         */
        template: require('tpl!orostylebook/templates/style-book/style-book-elements-nav-item.html'),

        /**
         * @property {String}
         */
        autoRender: true,

        /**
         * @property {Number}
         */
        offset: 0,

        /**
         * @property {Object}
         */
        options: {
            elementSelector: '[data-style-book-element]',
            itemSelector: '[data-style-book-element-item]',
            switchSelector: '[data-style-book-element-switch]',
            elementsContainerSelector: null,
            activeClass: 'active'
        },

        /**
         * @inheritDoc
         */
        listen: {
            'page:afterChange mediator': 'initState'
        },

        /**
         * @property {Object}
         */
        events: {
            'click [data-style-book-element-switch]': 'onSwitchClick'
        },

        /**
         * @property {Number}
         */
        pageScrollDuration: 20,

        /**
         * @inheritDoc
         */
        constructor: function StyleBookElementsNavigationView() {
            StyleBookElementsNavigationView.__super__.constructor.apply(this, arguments);
        },

        /**
         * @inheritDoc
         */
        initialize: function(options) {
            this.options = _.extend({}, this.options, options);

            this.template = this.options.template || this.template;

            this.offset = $(this.options.elementsContainerSelector).length
                ? $(this.options.elementsContainerSelector).offset().top
                : 0;

            this.initState();

            StyleBookElementsNavigationView.__super__.initialize.apply(this, arguments);
        },

        initState: function() {
            if (!_.isEmpty(window.location.hash)) {
                this.scrollToElement(window.location.hash);
            }
        },

        /**
         * @inheritDoc
         */
        onSwitchClick: function(e) {
            e.preventDefault();
            this.$el.find(this.options.itemSelector).removeClass(this.options.activeClass);
            $(e.target).parents().filter(this.options.itemSelector).addClass(this.options.activeClass);

            this.scrollToElement($(e.target).attr('href'));
        },

        /**
         *
         * @param $element
         */
        scrollToElement: function(anchor, speed) {
            var $element = $(anchor);
            var scrollPos = $element.offset().top - this.offset;

            $('body,html').animate({
                scrollTop: scrollPos
            }, this.pageScrollDuration);

            window.location.hash = anchor;
        },

        /**
         * @inheritDoc
         */
        getElementsList: function() {
            var $elemList = $(this.options.elementSelector);
            var items = [];
            $elemList.each(_.bind(function(index, elem) {
                items.push($(elem).data('style-book-element'));
            }, this));

            return items;
        },

        /**
         * @inheritDoc
         */
        render: function() {
            var self = this;

            this.$el.html(this.template({
                items: this.getElementsList()
            }));

            this.$el.find('[data-style-book-element-item]:first').addClass(this.options.activeClass);

            $('body').on({
                'activate.bs.scrollspy': function(e) {
                    $(e.target).parents().filter(self.options.itemSelector).addClass(self.options.activeClass);
                }
            }).scrollspy({target: '#' + this.$el.attr('id'), offset: (this.offset * 2 - 5)});
        }
    });

    return StyleBookElementsNavigationView;
});

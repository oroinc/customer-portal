define(function(require) {
    'use strict';

    const BaseView = require('oroui/js/app/views/base/view');
    const $ = require('jquery');
    const _ = require('underscore');

    require('bootstrap-scrollspy');

    const StyleBookElementsNavigationView = BaseView.extend({
        /**
         * @property {String}
         */
        template: require('tpl-loader!orostylebook/templates/style-book/style-book-elements-nav-item.html'),

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
            elementsContainerSelector: null,
            activeClass: 'active'
        },

        /**
         * @inheritdoc
         */
        listen: {
            'page:afterChange mediator': 'initState'
        },

        /**
         * @property {Object}
         */
        events: {
            'click .nav-link': 'onSwitchClick'
        },

        /**
         * @property {Number}
         */
        pageScrollDuration: 20,

        /**
         * @inheritdoc
         */
        constructor: function StyleBookElementsNavigationView(options) {
            StyleBookElementsNavigationView.__super__.constructor.call(this, options);
        },

        /**
         * @inheritdoc
         */
        initialize: function(options) {
            this.options = _.extend({}, this.options, options);

            this.template = this.options.template || this.template;

            this.offset = $(this.options.elementsContainerSelector).length
                ? $(this.options.elementsContainerSelector).offset().top
                : 0;

            this.initState();

            StyleBookElementsNavigationView.__super__.initialize.call(this, options);
        },

        initState: function() {
            if (!_.isEmpty(window.location.hash)) {
                this.scrollToElement(window.location.hash);
            }
        },

        /**
         * @inheritdoc
         */
        onSwitchClick: function(e) {
            e.preventDefault();
            this.$el.find(this.options.itemSelector).removeClass(this.options.activeClass);
            $(e.target).parents().filter(this.options.itemSelector).addClass(this.options.activeClass);

            this.scrollToElement($(e.target).attr('href'));
        },

        /**
         *
         * @param {String} anchor
         */
        scrollToElement: function(anchor) {
            const $element = $(anchor);
            if ($element.length) {
                const scrollPos = $element.offset().top - this.offset;

                $('body, html').animate({
                    scrollTop: scrollPos
                }, this.pageScrollDuration);

                window.location.hash = anchor;
            } else {
                // Clear hash from browser URI field
                history.replaceState(null, null, ' ');
            }
        },

        /**
         * @inheritdoc
         */
        getElementsList: function() {
            const $elemList = $(this.options.elementSelector);
            const items = [];
            $elemList.each((index, elem) => {
                items.push($(elem).data('style-book-element'));
            });

            return items;
        },

        /**
         * @inheritdoc
         */
        render: function() {
            this.$el.html(this.template({
                items: this.getElementsList()
            }));

            this.$el.find('.nav-link:first').addClass(this.options.activeClass);
            $('body').scrollspy({target: '#' + this.$el.attr('id'), offset: (this.offset * 2 - 5)});
        }
    });

    return StyleBookElementsNavigationView;
});

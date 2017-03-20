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
        template: require('tpl!orofrontend/templates/style-book/style-book-elements-nav-item.html'),

        /**
         * @property {String}
         */
        autoRender: true,

        /**
         * @property {Object}
         */
        options: {
            elementSelector: '[data-style-book-element]',
            itemSelector: '[data-style-book-element-item]',
            switchSelector: '[data-style-book-element-switch]',
            activeClass: 'active'
        },

        /**
         * @property {Object}
         */
        events: {
            'click [data-style-book-element-switch]': 'onSwitchClick'
        },

        /**
         * @inheritDoc
         */
        initialize: function(options) {
            this.options = _.extend({}, this.options, options);

            this.template = this.options.template || this.template;

            StyleBookElementsNavigationView.__super__.initialize.apply(this, arguments);
        },

        /**
         * @inheritDoc
         */
        onSwitchClick: function(e) {
            this.$el.find(this.options.itemSelector).removeClass(this.options.activeClass);
            $(e.target).parents().filter(this.options.itemSelector).addClass(this.options.activeClass);
        },

        /**
         * @inheritDoc
         */
        getElementsList: function() {
            var $elemList = $(this.options.elementSelector);
            var items = [];

            $elemList.each(function() {
                items.push($(this).data('style-book-element'));
            });

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
            }).scrollspy({target: '#' + this.$el.attr('id')});
        }
    });

    return StyleBookElementsNavigationView;
});

define(function(require) {
    'use strict';

    var HeaderRowComponent;
    var BaseComponent = require('oroui/js/app/components/base/component');
    var mediator = require('oroui/js/mediator');
    var tools = require('oroui/js/tools');
    var $ = require('jquery');
    var _ = require('underscore');

    HeaderRowComponent = BaseComponent.extend({
        /**
         * @property {jQuery}
         */
        $el: null,

        /**
         * @inheritDoc
         */
        constructor: function HeaderRowComponent() {
            HeaderRowComponent.__super__.constructor.apply(this, arguments);
        },

        /**
         * @inheritDoc
         */
        initialize: function(options) {
            this.$el = $(options._sourceElement);
            this.$mainMenuDropdown = this.$el.find('[data-header-row-toggle]');

            /**
             * Prevent to close Bootstrap dropdowns
             */
            this.$mainMenuDropdown.on('click', function(e) {
                e.stopPropagation();
            });
        },

        /**
         * @inheritDoc
         */
        delegateListeners: function() {
            if (tools.isMobile()) {
                this.listenTo(mediator, 'layout:reposition', _.debounce(this.addScroll, 40));
                this.listenTo(mediator, 'sticky-panel:toggle-state', _.debounce(this.addScroll, 40));
            }

            return HeaderRowComponent.__super__.delegateListeners.apply(this, arguments);
        },

        addScroll: function() {
            var windowHeight = $(window).innerHeight();
            var headerRowHeight = this.$el.height();
            var middleBarHeight = this.$el.closest('.page-container').find('.middlebar').outerHeight();
            var menuHeight = windowHeight - headerRowHeight;
            var isSticky = this.$el.hasClass('header-row--fixed');
            var $dropdowns = this.$el.find('.header-row__dropdown');

            if (!isSticky) {
                menuHeight = windowHeight - headerRowHeight - middleBarHeight;
            }
            $.each($dropdowns, function(index, dropdown) {
                $(dropdown).parent().removeAttr('style');

                var dropdownHeight = $(dropdown).height();

                if (dropdownHeight >= menuHeight) {
                    $(dropdown)
                        .parent()
                        .css('height', menuHeight);
                }
            });
        },

        dispose: function() {
            if (this.disposed) {
                return;
            }

            this.$mainMenuDropdown.off('click');

            delete this.$mainMenuDropdown;
            delete this.$el;

            HeaderRowComponent.__super__.dispose.call(this);
        }
    });

    return HeaderRowComponent;
});

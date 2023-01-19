define(function(require) {
    'use strict';

    const BaseView = require('oroui/js/app/views/base/view');
    const viewportManager = require('oroui/js/viewport-manager').default;
    const scrollHelper = require('oroui/js/tools/scroll-helper');
    const $ = require('jquery');
    const _ = require('underscore');

    const HeaderRowView = BaseView.extend({
        optionNames: BaseView.prototype.optionNames.concat([
            'useScrollViewport'
        ]),

        events: {
            'touchstart .header-row__container [data-scroll="true"]': 'onTouchScrollingContainer',
            'shown.bs.dropdown': 'onDropdownShow',
            'hide.bs.dropdown': 'onDropdownHide',
            'click [data-header-row-toggle]': 'onToggleClick'
        },

        listen() {
            return {
                [`viewport:${this.useScrollViewport} mediator`]: 'checkViewport'
            };
        },

        useScrollViewport: 'tablet',

        isUseScroll: false,

        animationFrameId: null,

        cache: null,

        /**
         * @inheritdoc
         */
        constructor: function HeaderRowView(options) {
            HeaderRowView.__super__.constructor.call(this, options);
        },

        /**
         * @inheritdoc
         */
        initialize: function(options) {
            this.checkViewport();
            return HeaderRowView.__super__.initialize.call(this, options);
        },

        checkViewport: function() {
            this.isUseScroll = viewportManager.isApplicable(this.useScrollViewport);
        },

        onToggleClick: function(e) {
            // prevent to close Bootstrap dropdowns
            e.stopPropagation();
        },

        onTouchScrollingContainer: function(e) {
            if (!this.isUseScroll) {
                return;
            }
            scrollHelper.removeIOSRubberEffect(e);
        },

        onDropdownShow: function(e) {
            this.removeScroll();
            this.updateCache(e);
            this.updateDropdown();
        },

        onDropdownHide: function(e) {
            this.removeScroll();
            this.clearCache();
        },

        updateCache: function(e) {
            this.clearCache();

            this.cache.$body = $('body');
            this.cache.$middleBar = this.$el.parent().prev();
            this.cache.$row = $(e.target);
            this.cache.$dropdown = this.cache.$row.find('.header-row__dropdown');
            this.cache.$dropdownFooter = this.cache.$dropdown.find('.header-row__dropdown-footer');
            this.cache.$dropdownScrollable = this.cache.$dropdown.find('[data-scroll]');
            if (!this.cache.$dropdownScrollable.length) {
                this.cache.$dropdownScrollable = this.cache.$dropdown.parent();
            }
        },

        clearCache: function() {
            this.cache = {};
        },

        updateDropdown: function() {
            if (this.isUseScroll) {
                this.updateScroll();
            } else {
                this.removeScroll();
            }

            const self = this;
            this.animationFrameId = requestAnimationFrame(function() {
                self.updateDropdown();
            });
        },

        updateScroll: function() {
            let menuHeight = window.innerHeight - this.$el.height();
            if (!(this.$el.data('sticky') || {}).currentState) {
                menuHeight -= this.cache.$middleBar.outerHeight();
            }

            const dropdownHeight = this.cache.$dropdown.outerHeight();
            if (dropdownHeight < menuHeight) {
                return this.removeScroll();
            }

            const footerHeight = this.cache.$dropdownFooter.outerHeight() || 0;

            this.cache.$dropdownScrollable.css({
                height: menuHeight - footerHeight,
                overflowY: 'auto'
            });

            this.cache.$body.addClass('no-scroll');
        },

        removeScroll: function() {
            if (this.animationFrameId) {
                cancelAnimationFrame(this.animationFrameId);
            }

            if (_.isEmpty(this.cache)) {
                return;
            }

            this.cache.$dropdownScrollable.css({
                height: '',
                overflowY: ''
            }).removeAttr('style');

            this.cache.$body.removeClass('no-scroll');
        },

        dispose: function() {
            if (this.disposed) {
                return;
            }

            this.removeScroll();
            this.clearCache();

            return HeaderRowView.__super__.dispose.call(this);
        }
    });

    return HeaderRowView;
});

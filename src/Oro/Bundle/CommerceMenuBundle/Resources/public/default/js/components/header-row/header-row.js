import BaseComponent from 'oroui/js/app/components/base/component';
import mediator from 'oroui/js/mediator';
import tools from 'oroui/js/tools';
import $ from 'jquery';
import _ from 'underscore';

const HeaderRowComponent = BaseComponent.extend({
    /**
     * @property {jQuery}
     */
    $el: null,

    /**
     * @inheritdoc
     */
    constructor: function HeaderRowComponent(options) {
        HeaderRowComponent.__super__.constructor.call(this, options);
    },

    /**
     * @inheritdoc
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
     * @inheritdoc
     */
    delegateListeners: function() {
        if (tools.isMobile()) {
            this.listenTo(mediator, 'layout:reposition', _.debounce(this.addScroll, 40));
            this.listenTo(mediator, 'sticky-panel:toggle-state', _.debounce(this.addScroll, 40));
        }

        return HeaderRowComponent.__super__.delegateListeners.call(this);
    },

    addScroll: function() {
        const windowHeight = $(window).innerHeight();
        const headerRowHeight = this.$el.height();
        const middleBarHeight = this.$el.closest('.page-container').find('.middlebar').outerHeight();
        let menuHeight = windowHeight - headerRowHeight;
        const isSticky = this.$el.hasClass('header-row--fixed');
        const $dropdowns = this.$el.find('.header-row__dropdown');

        if (!isSticky) {
            menuHeight = windowHeight - headerRowHeight - middleBarHeight;
        }
        $.each($dropdowns, function(index, dropdown) {
            $(dropdown).parent().attr('style', null);

            const dropdownHeight = $(dropdown).height();

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

export default HeaderRowComponent;

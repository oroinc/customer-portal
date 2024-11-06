import $ from 'jquery';
import _ from 'underscore';
import InputWidgetManager from 'oroui/js/input-widget-manager';
import AbstractInputWidgetView from 'oroui/js/app/views/input-widget/abstract';
import viewportManager from 'oroui/js/viewport-manager';

const ResponsiveStyler = AbstractInputWidgetView.extend({
    /**
     * @property {RegExp}
     */
    classesRegex: /^btn/,

    /**
     * @inheritdoc
     */
    listen() {
        const events = {};

        if (this.responsive) {
            events['viewport:change mediator'] = 'changeAppearance';
        }

        return events;
    },

    /**
     * @inheritdoc
     */
    constructor: function ResponsiveStyler(options) {
        this.UID = _.uniqueId('widget-');
        ResponsiveStyler.__super__.constructor.call(this, options);
    },

    /**
     * Find widget root element
     * @returns {jQuery.Element}
     */
    findContainer() {
        return this.$el;
    },

    /**
     * Nothing to do
     * @inheritdoc
     */
    widgetFunction() {
        return this;
    },

    /**
     * @inheritdoc
     */
    initialize(options) {
        this.saveOriginalDesign();

        ResponsiveStyler.__super__.initialize.call(this, options);

        if (!this.responsive) {
            console.warn('Option "responsive" should be declare, otherwise this widget does nothing');
        }

        this.changeAppearance();
    },

    /**
     * Save original widget's design
     */
    saveOriginalDesign() {
        const $icon = this.getIcon();
        const hasTooltip = this.hasTooltip();

        this._original = {
            'class': this.$el.attr('class'),
            hasTooltip,
            'tooltipOptions': this.$el.data('tooltip')
        };

        if ($icon.length) {
            this._original['$icon'] = $icon.clone();
        }
    },

    /**
     * Set original widget's design
     */
    restoreOriginalDesign() {
        if (!this._original) {
            return;
        }

        if (this._original.class) {
            this.$el.attr('class', this._original.class);
        }

        if (this._original.$icon) {
            const $icon = this.getIcon();

            $icon.replaceWith(this._original.$icon);
        }

        if (this._original.hasTooltip) {
            this.initToolTip();
        }

        this.$el.removeData('responsiveAppearance');
        this.$el.trigger('content:changed');
    },

    /**
     * Changes widget's design
     */
    changeAppearance() {
        if (!this.responsive || this.disposed) {
            return;
        }

        const breakpoint = viewportManager.getApplicableBreakpointName(this.getBreakpoints());

        if (breakpoint === this.$el.data('responsiveAppearance')) {
            return;
        }

        this.restoreOriginalDesign();

        if (!breakpoint || !viewportManager.isApplicable(breakpoint)) {
            this.$el.removeData('responsiveAppearance');
            return;
        }

        this.$el.data('responsiveAppearance', breakpoint);
        this.applyDesign(this.responsive[breakpoint]);
    },

    /**
     * Apply design depend on viewport
     * @param {Object} data
     */
    applyDesign(data) {
        const {classes, icon, iconClass, disposeTooltip = false, initTooltip = false} = data;

        this.setClass(classes);
        this.setIcon(icon, iconClass);

        if (disposeTooltip) {
            this.disposeTooltip();
        }

        if (initTooltip) {
            this.initToolTip();
        }

        this.$el.trigger('content:changed');
    },

    /**
     * @param {string} classes
     */
    setClass(classes) {
        if (!classes) {
            return;
        }

        if (this._original?.class) {
            this.$el.attr('class', this._original.class);
        }

        this.$el
            .removeClass(this.getMatchClasses())
            .addClass(classes);
    },

    /**
     * @param {string } icon
     * @param {string } [iconClass]
     */
    setIcon(icon, iconClass) {
        if (!icon) {
            return;
        }

        const newIcon = _.macros('oroui::renderIcon')({
            name: icon, extraClass: iconClass ?? ''
        }).trim();
        this.getIcon().replaceWith(newIcon);
    },

    /**
     * Destroy bootstrap tooltip
     */
    disposeTooltip() {
        this.$el.tooltip('dispose');
        this.$el.removeAttr('data-tooltip');
        if (this.$el.is('[data-toggle="tooltip"]')) {
            this.$el.removeAttr('data-toggle');
        }
    },

    /**
     * Initialize bootstrap tooltip
     */
    initToolTip() {
        // An element does not have any bootstrap components initialized
        if (!this.$el.is('[data-toggle]')) {
            this.$el.attr('data-toggle', 'tooltip');
        }

        if (this._original.tooltipOptions) {
            this.$el.attr('data-tooltip', JSON.stringify(this._original.tooltipOptions));
        }

        this.$el.tooltip(this.$el.data('tooltip') || {});
    },

    /**
     * Gets classes to replace
     * @returns {string}
     */
    getMatchClasses() {
        return this.$el.attr('class').split(' ').filter(cl => this.classesRegex.test(cl)).join(' ');
    },

    /**
     * Gens an icon element
     * @returns {jquery.Element}
     */
    getIcon() {
        return this.$el.find('.theme-icon');
    },

    /**
     * Gets available breakpoints
     * @returns {string[]}
     */
    getBreakpoints() {
        if (!this.responsive) {
            return [];
        }

        return Object.keys(this.responsive);
    },

    /**
     * @returns {boolean}
     */
    hasTooltip() {
        return Boolean(this.$el.is('[data-toggle="tooltip"]') || this.$el.data('bs.tooltip'));
    },

    /**
     * Destroy widget
     * @inheritdoc
     */
    dispose: function() {
        if (this.disposed) {
            return;
        }

        this.restoreOriginalDesign();

        return ResponsiveStyler.__super__.dispose.call(this);
    }
});

$(document).on('operation-button:init', e => {
    const $el = $(e.target);
    if ($el.is('[data-responsive-styler]')) {
        $el.inputWidget(InputWidgetManager.hasWidget($el) ? 'refresh' : 'create');
    }
}).on('operation-button:dispose', e => {
    const $el = $(e.target);
    if (
        $el.is('[data-responsive-styler]') &&
        InputWidgetManager.hasWidget($el)
    ) {
        $el.inputWidget('seekAndDestroy');
    }
});

export default ResponsiveStyler;

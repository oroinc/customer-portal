import template from 'tpl-loader!orofrontend/templates/fullscreen-popup/fullscreen-popup.html';
import footerTemplate from 'tpl-loader!orofrontend/templates/fullscreen-popup/fullscreen-popup-footer.html';
import headerTemplate from 'tpl-loader!orofrontend/templates/fullscreen-popup/fullscreen-popup-header.html';
import BaseView from 'oroui/js/app/views/base/view';
import loadModules from 'oroui/js/app/services/load-modules';
import mediator from 'oroui/js/mediator';
import scrollHelper from 'oroui/js/tools/scroll-helper';
import _ from 'underscore';
import $ from 'jquery';
import manageFocus from 'oroui/js/tools/manage-focus';

const ESCAPE_KEYCODE = 27;

const FullscreenPopupView = BaseView.extend({
    /**
     * @property
     */
    keepElement: true,

    /**
     * @property
     */
    optionNames: BaseView.prototype.optionNames.concat([
        'template', 'templateSelector', 'templateData',
        'popupName', 'popupLabel', 'popupCloseOnLabel',
        'popupCloseButton', 'popupIcon',
        'stopEventsPropagation', 'stopEventsList', 'dialogClass',
        'disableBodyTouchScroll'
    ]),

    popupName: 'fullscreen-popup',

    sections: ['header', 'content', 'footer'],

    sectionOptionVariants: ['', 'Selector', 'View', 'Element', 'Template'],

    header: {
        Template: headerTemplate,
        options: {
            templateData: {}
        }
    },

    footer: {
        Template: footerTemplate
    },

    /**
     * @property
     */
    template: template,

    /**
     * @property
     */
    popupLabel: _.__('Back'),

    /**
     * @property
     */
    popupCloseOnLabel: true,

    /**
     * @property
     */
    popupCloseButton: true,

    /**
     * @property
     */
    popupIcon: false,

    /**
     * @property
     */
    previousClass: null,

    events: {
        click: 'show'
    },

    /**
     * @property
     */
    stopEventsPropagation: true,

    /**
     * @property
     */
    stopEventsList: 'mousedown focusin',

    /**
     * @property
     */
    $popup: null,

    /**
     * @property
     */
    dialogClass: '',

    disableBodyTouchScroll: true,

    /**
     * Add when popup showed
     * @property {string}
     */
    toggleBtnActiveClassName: 'fullscreen-popup__opened',

    container: document.body,

    /**
     * @inheritdoc
     */
    constructor: function FullscreenPopupView(options) {
        FullscreenPopupView.__super__.constructor.call(this, options);
    },

    /**
     * @inheritdoc
     */
    initialize: function(options) {
        this.initLayoutOptions = options.initLayoutOptions || {};
        _.each(this.sections, this._initializeSection.bind(this, options));
        return FullscreenPopupView.__super__.initialize.call(this, options);
    },

    /**
     * @inheritdoc
     */
    dispose: function() {
        if (this.disposed) {
            return;
        }
        this.remove();
        FullscreenPopupView.__super__.dispose.call(this);
    },

    appendToContainer() {
        return this.container;
    },

    show: function() {
        this.close();
        this.$popup = $(this.getTemplateFunction()(this.getTemplateData()));

        this.$popup.appendTo(this.appendToContainer());

        const promises = _.map(this.sections, this.showSection, this);
        $.when(...promises).then(this._onShow.bind(this));
    },

    showSection: function(section) {
        const deferred = $.Deferred();
        this[section].$el = this.$popup.find('[data-role="' + section + '"]');
        if (false === this._eachSectionVariant(section, '_renderSection', deferred)) {
            deferred.resolve();
        }
        return deferred.promise();
    },

    /**
     * @inheritdoc
     */
    getTemplateData: function() {
        let data = FullscreenPopupView.__super__.getTemplateData.call(this);
        data = _.extend({}, data, {
            id: this.cid,
            close: this.popupCloseButton,
            dialogClass: this.dialogClass
        });
        return data;
    },

    /**
     * @param {String} title
     */
    setPopupTitle: function(title) {
        if (this.$popup) {
            this.$popup.find('[data-role="title"]').html(title);
        }
    },

    close: function() {
        if (!this.$popup) {
            return;
        }

        this.trigger('beforeclose');

        this.remove();

        this.trigger('close');
        mediator.trigger('fullscreen:popup:close', this);
    },

    remove() {
        if (!this.$popup) {
            return;
        }

        if (this.disableBodyTouchScroll) {
            scrollHelper.enableBodyTouchScroll();
        }

        _.each(this.sections, this.closeSection, this);

        this.$popup.find('[data-scroll="true"]').off('touchstart');
        this.$popup.trigger(`${this.popupName}:closed`);
        this.$popup.remove();
        delete this.$popup;

        this.$el.removeClass(this.toggleBtnActiveClassName);
    },

    getLayoutElement() {
        return this.$popup;
    },

    closeSection: function(section) {
        this._eachSectionVariant(section, '_closeSection');
    },

    _eachSectionVariant: function(section, callback, ...args) {
        const sectionOptions = this[section];
        let variant;
        for (let i = 0; i < this.sectionOptionVariants.length; i++) {
            variant = this.sectionOptionVariants[i];
            const func = callback + variant;
            const variantValue = sectionOptions[variant];
            if (this[func] && variantValue !== null) {
                return this[func](...args.concat([
                    section,
                    sectionOptions,
                    variant,
                    variantValue
                ]));
            }
        }
    },

    _initializeSection: function(options, section) {
        const sectionOptions = this[section] = _.extend({}, this[section] || {});
        sectionOptions.options = _.extend({}, sectionOptions.options || {}, options[section + 'Options'] || {});
        sectionOptions.attr = options[section + 'Attributes'] || {};

        this.sectionOptionVariants = _.map(this.sectionOptionVariants, function(variant) {
            const value = options[section + variant];
            variant = variant || 'Content';
            sectionOptions[variant] = value !== undefined ? value : (sectionOptions[variant] || null);

            return variant;
        }, this);

        if (section === 'header' && _.isObject(sectionOptions.options.templateData)) {
            sectionOptions.options.templateData = _.extend({
                id: this.cid,
                label: this.popupLabel,
                closeOnLabel: this.popupCloseOnLabel,
                icon: this.popupIcon
            }, sectionOptions.options.templateData);
        }
    },

    _onShow: function() {
        this._initPopupEvents();
        this.initLayout(this.initLayoutOptions);
        manageFocus.focusTabbable(this.getFocusTabbableElement());
        mediator.trigger('layout:reposition');

        if (this.disableBodyTouchScroll) {
            scrollHelper.disableBodyTouchScroll();
        }

        this.trigger('show');
        this.$popup.trigger(`${this.popupName}:shown`);

        this.$el.addClass(this.toggleBtnActiveClassName);

        mediator.trigger('fullscreen:popup:show', this);
    },

    _renderSectionContent: function(deferred, section, sectionOptions, option, content) {
        sectionOptions.$el.html(content);
        deferred.resolve();
    },

    _renderSectionElement: function(deferred, section, sectionOptions, option, element) {
        const $element = $(element);
        sectionOptions.$placeholder = $('<div/>');

        this._savePreviousClasses($element);
        $element.after(sectionOptions.$placeholder)
            .attr(sectionOptions.attr);
        sectionOptions.$el.append($element);

        deferred.resolve();
    },

    _renderSectionSelector: function(deferred, section, sectionOptions, option, selector) {
        return this._renderSectionContent(deferred, section, sectionOptions, option, $(selector).html());
    },

    _renderSectionView: function(deferred, section, sectionOptions, option, View) {
        if (_.isString(View)) {
            loadModules(View, function(View) {
                sectionOptions.View = View;
                this._renderSectionView(deferred, section, sectionOptions, option, View);
            }, this);
        } else {
            this.subview(section, new View(
                _.extend(this[section].options, {
                    el: sectionOptions.$el.get()
                })
            ));
            deferred.resolve();
        }
    },

    _renderSectionTemplate: function(deferred, section, sectionOptions, option, template) {
        const templateData = sectionOptions.options.templateData;
        if (!templateData) {
            return false;
        }
        return this._renderSectionContent(deferred, section, sectionOptions, option, template(templateData));
    },

    _closeSectionElement: function(section, sectionOptions, option, element) {
        if (!sectionOptions.$placeholder) {
            return;
        }
        const $element = $(element);

        sectionOptions.$placeholder
            .after($element)
            .remove();

        $element.removeAttr(_.keys(sectionOptions.attr).join(' '));
        this._setPreviousClasses($element);
    },

    _closeSectionView: function(section, sectionOptions, option, View) {
        this.removeSubview(section);
    },

    _initPopupEvents: function() {
        this.$popup
            .on('click', '[data-role="close"]', this.close.bind(this))
            .on('touchstart', '[data-scroll="true"]', scrollHelper.removeIOSRubberEffect.bind(this))
            .on('keydown', e => {
                if (e.keyCode === ESCAPE_KEYCODE) {
                    e.stopPropagation();
                    this.close();
                    this.$el.trigger('focus');
                } else {
                    manageFocus.preventTabOutOfContainer(e, this.$popup);
                }
            })
            .one('transitionend', e => {
                if (e.target === this.$popup[0]) {
                    manageFocus.focusTabbable(this.getFocusTabbableElement());
                }
            });

        if (this.stopEventsPropagation) {
            this.$popup.on(this.stopEventsList, function(e) {
                e.stopPropagation();
            });
        }
    },

    /**
     * @param {jQuery} $el
     */
    _savePreviousClasses: function($el) {
        this.previousClass = $el.attr('class');
    },

    /**
     * @param {jQuery} $el
     */
    _setPreviousClasses: function($el) {
        $el.attr('class', this.previousClass);
    },

    /**
     * Return element for focus after show
     * @returns {jQuery.Element}
     */
    getFocusTabbableElement() {
        return this.$popup;
    }
});

export default FullscreenPopupView;

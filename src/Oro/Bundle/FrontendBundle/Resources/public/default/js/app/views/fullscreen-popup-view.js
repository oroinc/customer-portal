define(function(require) {
    'use strict';

    const template = require('tpl-loader!orofrontend/templates/fullscreen-popup/fullscreen-popup.html');
    const footerTemplate = require('tpl-loader!orofrontend/templates/fullscreen-popup/fullscreen-popup-footer.html');
    const headerTemplate = require('tpl-loader!orofrontend/templates/fullscreen-popup/fullscreen-popup-header.html');
    const BaseView = require('oroui/js/app/views/base/view');
    const loadModules = require('oroui/js/app/services/load-modules');
    const mediator = require('oroui/js/mediator');
    const scrollHelper = require('oroui/js/tools/scroll-helper');
    const _ = require('underscore');
    const $ = require('jquery');
    const manageFocus = require('oroui/js/tools/manage-focus').default;

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
            'popupLabel', 'popupCloseOnLabel',
            'popupCloseButton', 'popupIcon', 'popupBadge',
            'stopEventsPropagation', 'stopEventsList'
        ]),

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
        popupBadge: false,

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
         * @inheritdoc
         */
        constructor: function FullscreenPopupView(options) {
            FullscreenPopupView.__super__.constructor.call(this, options);
        },

        /**
         * @inheritdoc
         */
        initialize: function(options) {
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
            this.close();
            FullscreenPopupView.__super__.dispose.call(this);
        },

        show: function() {
            this.close();
            this.$popup = $(this.getTemplateFunction()(this.getTemplateData()));

            this.$popup.appendTo($('body'));

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
                close: this.popupCloseButton
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

            scrollHelper.enableBodyTouchScroll();

            _.each(this.sections, this.closeSection, this);

            this.$popup.find('[data-scroll="true"]').off('touchstart');
            this.$popup.remove();
            delete this.$popup;

            this.trigger('close');
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
                    icon: this.popupIcon,
                    badge: this.popupBadge
                }, sectionOptions.options.templateData);
            }
        },

        _onShow: function() {
            this._initPopupEvents();
            manageFocus.focusTabbable(this.$popup);
            mediator.trigger('layout:reposition');
            scrollHelper.disableBodyTouchScroll();
            this.trigger('show');
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
                .on('keydown', event => manageFocus.preventTabOutOfContainer(event, this.$popup))
                .one('transitionend', e => {
                    if (e.target === this.$popup[0]) {
                        manageFocus.focusTabbable(this.$popup);
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
        }
    });

    return FullscreenPopupView;
});

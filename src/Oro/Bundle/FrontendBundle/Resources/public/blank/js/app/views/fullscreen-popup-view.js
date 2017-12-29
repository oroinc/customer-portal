define(function(require) {
    'use strict';

    var FullscreenPopupView;
    var template = require('tpl!orofrontend/templates/fullscreen-popup/fullscreen-popup.html');
    var footerTemplate = require('tpl!orofrontend/templates/fullscreen-popup/fullscreen-popup-footer.html');
    var headerTemplate = require('tpl!orofrontend/templates/fullscreen-popup/fullscreen-popup-header.html');
    var BaseView = require('oroui/js/app/views/base/view');
    var tools = require('oroui/js/tools');
    var mediator = require('oroui/js/mediator');
    var scrollHelper = require('oroui/js/tools/scroll-helper');
    var _ = require('underscore');
    var $ = require('jquery');

    FullscreenPopupView = BaseView.extend({
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
            'click': 'show'
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
         * @inheritDoc
         */
        initialize: function(options) {
            _.each(this.sections, this._initializeSection.bind(this, options));
            return FullscreenPopupView.__super__.initialize.apply(this, arguments);
        },

        /**
         * @inheritDoc
         */
        dispose: function() {
            if (this.disposed) {
                return;
            }
            this.close();
            FullscreenPopupView.__super__.dispose.apply(this, arguments);
        },

        show: function() {
            this.close();
            this.$popup = $(this.getTemplateFunction()(this.getTemplateData()));

            this.$popup.appendTo($('body'));

            var promises = _.map(this.sections, this.showSection, this);
            $.when.apply($, promises).then(this._onShow.bind(this));
        },

        showSection: function(section) {
            var deferred = $.Deferred();
            this[section].$el = this.$popup.find('[data-role="' + section + '"]');
            if (false === this._eachSectionVariant(section, '_renderSection', deferred)) {
                deferred.resolve();
            }
            return deferred.promise();
        },

        /**
         * @inheritDoc
         */
        getTemplateData: function() {
            var data = FullscreenPopupView.__super__.getTemplateData.apply(this, arguments);
            data = _.extend({}, data, {
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

        _eachSectionVariant: function(section, callback) {
            var args = _.rest(arguments, 2);
            var sectionOptions = this[section];
            var variant;
            for (var i = 0; i < this.sectionOptionVariants.length; i++) {
                variant = this.sectionOptionVariants[i];
                var func = callback + variant;
                var variantValue = sectionOptions[variant];
                if (this[func] && variantValue !== null) {
                    return this[func].apply(this, args.concat([
                        section,
                        sectionOptions,
                        variant,
                        variantValue
                    ]));
                }
            }
        },

        _initializeSection: function(options, section) {
            var sectionOptions = this[section] = _.extend({}, this[section] || {});
            sectionOptions.options = _.extend(sectionOptions.options || {}, options[section + 'Options'] || {});
            sectionOptions.attr = options[section + 'Attributes'] || {};

            this.sectionOptionVariants = _.map(this.sectionOptionVariants, function(variant) {
                var value = options[section + variant];
                variant = variant || 'Content';
                sectionOptions[variant] = value !== undefined ? value : (sectionOptions[variant] || null);

                return variant;
            }, this);

            if (section === 'header' && _.isObject(sectionOptions.options.templateData)) {
                sectionOptions.options.templateData = _.extend({
                    label: this.popupLabel,
                    closeOnLabel: this.popupCloseOnLabel,
                    icon: this.popupIcon,
                    badge: this.popupBadge
                }, sectionOptions.options.templateData);
            }
        },

        _onShow: function() {
            this._initPopupEvents();
            mediator.trigger('layout:reposition');
            scrollHelper.disableBodyTouchScroll();
            this.trigger('show');
        },

        _renderSectionContent: function(deferred, section, sectionOptions, option, content) {
            sectionOptions.$el.html(content);
            deferred.resolve();
        },

        _renderSectionElement: function(deferred, section, sectionOptions, option, element) {
            var $element = $(element);
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
                tools.loadModules(View, _.bind(function(View) {
                    sectionOptions.View = View;
                    this._renderSectionView(deferred, section, View);
                }, this));
            } else {
                this.subview(section, new View(this[section].options));
                deferred.resolve();
            }
        },

        _renderSectionTemplate: function(deferred, section, sectionOptions, option, template) {
            var templateData = sectionOptions.options.templateData;
            if (!templateData) {
                return false;
            }
            return this._renderSectionContent(deferred, section, sectionOptions, option, template(templateData));
        },

        _closeSectionElement: function(section, sectionOptions, option, element) {
            if (!sectionOptions.$placeholder) {
                return;
            }
            var $element = $(element);

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
                .on('click', '[data-role="close"]', _.bind(this.close, this))
                .on('touchstart', '[data-scroll="true"]', _.bind(scrollHelper.removeIOSRubberEffect, this));

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

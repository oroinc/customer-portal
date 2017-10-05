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
            'template', 'templateSelector', 'templateData', 'footerTemplate',
            'content', 'contentSelector', 'contentView',
            'contentOptions', 'contentElement', 'contentAttributes',
            'footerContentOptions',
            'headerTemplate', 'headerContentOptions',
            'previousClass', 'popupLabel', 'popupCloseOnLabel',
            'popupCloseButton', 'popupIcon', 'popupBadge',
            'stopEventsPropagation', 'stopEventsList'
        ]),

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
        content: null,

        /**
         * @property
         */
        contentElement: null,

        /**
         * @property
         */
        contentElementPlaceholder: null,

        /**
         * @property
         */
        previousClass: null,

        /**
         * @property
         */
        contentSelector: null,

        /**
         * @property
         */
        contentView: null,

        /**
         * @property
         */
        contentOptions: null,

        /**
         * @property
         */
        contentAttributes: {},

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
         * @property
         * @type {object}
         */
        headerContentOptions: null,

        /**
         * Data example for default template
         *  buttons: [{
         *      type: 'button',
         *      class: 'btn btn--info',
         *      role: 'action',
         *      label: 'Close'
         *  }]
         *
         * @property
         * @type {object}
         */
        footerContentOptions: null,

        /**
         * @property
         * @type {object}
         */
        footerTemplate: footerTemplate,

        /**
         * @property
         * @type {object}
         */
        headerTemplate: headerTemplate,

        /**
         * @inheritDoc
         */
        initialize: function(options) {
            this.savePreviousClasses($(this.contentElement));
            FullscreenPopupView.__super__.initialize.apply(this, arguments);
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

            this.contentOptions = _.extend({}, this.contentOptions || {}, {
                el: this.$popup.find('[data-role="content"]').get(0),
                headerEl: this.$popup.find('[data-role="header"]').get(0),
                footerEl: this.$popup.find('[data-role="footer"]').get(0)
            });

            if (this.footerContentOptions) {
                this.renderPopupFooterContent();
            }

            if (this.headerContentOptions) {
                this.renderPopupHeaderContent();
            }

            this.$popup.appendTo($('body'));

            this.renderPopupContent(_.bind(this.onShow, this));
        },

        onShow: function() {
            this.initPopupEvents();
            mediator.trigger('layout:reposition');
            scrollHelper.disableBodyTouchScroll();
            this.trigger('show');
        },

        renderPopupContent: function(callback) {
            if (this.content) {
                this.renderContent(callback);
            } else if (this.contentElement) {
                this.moveContentElement(callback);
            } else if (this.contentSelector) {
                this.renderSelectorContent(callback);
            } else if (this.contentView) {
                this.renderPopupView(callback);
            } else {
                callback();
            }
        },

        renderPopupHeaderContent: function() {
            $(this.contentOptions.headerEl).html(
                this.getTemplateFunction('headerTemplate')(this.headerContentOptions)
            );
        },

        renderPopupFooterContent: function(content) {
            content = content || '';
            if (!content && !_.isEmpty(this.footerContentOptions)) {
                content = this.getTemplateFunction('footerTemplate')(this.footerContentOptions);
            }
            $(this.contentOptions.footerEl).html(content);
        },

        renderContent: function(callback) {
            $(this.contentOptions.el).html(this.content);
            callback();
        },

        moveContentElement: function(callback) {
            this.contentElementPlaceholder = $('<div/>');
            $(this.contentElement).after(this.contentElementPlaceholder);
            $(this.contentOptions.el)
                .append(
                    $(this.contentElement).attr(this.contentAttributes)
                );
            callback();
        },

        renderSelectorContent: function(callback) {
            var content = $(this.contentSelector).html();
            $(this.contentOptions.el).html(content);
            callback();
        },

        renderPopupView: function(callback) {
            if (_.isString(this.contentView)) {
                tools.loadModules(this.contentView, _.bind(function(View) {
                    this.contentView = View;
                    this.renderPopupView(callback);
                }, this));
            } else {
                this.subview('contentView', new this.contentView(this.contentOptions));
                callback();
            }
        },

        initPopupEvents: function() {
            this.$popup
                .on('click', '[data-role="close"]', _.bind(this.close, this))
                .on('touchstart', '[data-scroll="true"]', _.bind(scrollHelper.removeIOSRubberEffect, this));

            if (this.stopEventsPropagation) {
                this.$popup.on(this.stopEventsList, function(e) {
                    e.stopPropagation();
                });
            }
        },

        close: function() {
            if (!this.$popup) {
                return;
            }

            scrollHelper.enableBodyTouchScroll();

            if (this.contentElement && this.contentElementPlaceholder) {
                $(this.contentElement).removeAttr(
                    _.keys(this.contentAttributes).join(' ')
                );
                this.setPreviousClasses($(this.contentElement));
                this.contentElementPlaceholder.after($(this.contentElement));
                this.contentElementPlaceholder.remove();
            }

            this.$popup.find('[data-scroll="true"]').off('touchstart');
            this.$popup.remove();

            delete this.$popup;

            this.removeSubview('contentView');
            this.trigger('close');
        },

        /**
         * @inheritDoc
         */
        getTemplateData: function() {
            var data = FullscreenPopupView.__super__.getTemplateData.apply(this, arguments);
            data = _.extend({}, data, {
                label: this.popupLabel,
                closeOnLabel: this.popupCloseOnLabel,
                close: this.popupCloseButton,
                icon: this.popupIcon,
                badge: this.popupBadge,
                footerContentOptions: this.footerContentOptions
            });
            return data;
        },

        /**
         * @param {jQuery} $el
         */
        savePreviousClasses: function($el) {
            this.previousClass = $el.attr('class');
        },

        /**
         * @param {jQuery} $el
         */
        setPreviousClasses: function($el) {
            $el.attr('class', this.previousClass);
        },

        /**
         * @param {String} title
         */
        setPopupTitle: function(title) {
            if (this.$popup) {
                this.$popup.find('[data-role="title"]').html(title);
            }
        }
    });

    return FullscreenPopupView;
});

define(function(require) {
    'use strict';

    var FullscreenPopupView;
    var template = require('tpl!orofrontend/templates/fullscreen-popup/fullscreen-popup.html');
    var BaseView = require('oroui/js/app/views/base/view');
    var tools = require('oroui/js/tools');
    var mediator = require('oroui/js/mediator');
    var scrollHelper = require('oroui/js/tools/scroll-helper');
    var _ = require('underscore');
    var $ = require('jquery');

    FullscreenPopupView = BaseView.extend({
        keepElement: true,

        optionNames: BaseView.prototype.optionNames.concat([
            'template', 'templateSelector', 'templateData',
            'content', 'contentSelector', 'contentView',
            'contentOptions', 'contentElement', 'contentAttributes',
            'previousClass', 'popupLabel', 'popupCloseOnLabel',
            'popupCloseButton', 'popupIcon', 'popupBadge'
        ]),

        template: template,

        popupLabel: _.__('Back'),

        popupCloseOnLabel: true,

        popupCloseButton: true,

        popupIcon: false,

        popupBadge: false,

        content: null,

        contentElement: null,

        contentElementPlaceholder: null,

        previousClass: null,

        contentSelector: null,

        contentView: null,

        contentOptions: null,

        contentAttributes: {},

        events: {
            'click': 'show'
        },

        $popup: null,

        /**
         * @inheritDoc
         */
        initialize: function() {
            this.savePreviousClasses($(this.contentElement));

            FullscreenPopupView.__super__.initialize.apply(this, arguments);
        },

        /**
         * @inheritDoc
         */
        dispose: function() {
            this.close();
            FullscreenPopupView.__super__.dispose.apply(this, arguments);
        },

        show: function() {
            this.close();
            this.$popup = $(this.getTemplateFunction()(this.getTemplateData()));

            this.contentOptions = _.extend({}, this.contentOptions || {}, {
                el: this.$popup.find('[data-role="content"]').get(0)
            });

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
            }else if (this.contentView) {
                this.renderPopupView(callback);
            } else {
                callback();
            }
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
            this.$popup.on('click', '[data-role="close"]', _.bind(this.close, this));
            this.$popup.on('touchstart', '[data-scroll="true"]', _.bind(scrollHelper.removeIOSRubberEffect, this));
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
                badge: this.popupBadge
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
        }
    });

    return FullscreenPopupView;
});

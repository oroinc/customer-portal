define(function(require) {
    'use strict';

    const DialogWidget = require('oro/dialog-widget');
    const actionsTemplate = require('tpl-loader!orofrontend/templates/frontend-dialog/dialog-actions.html');
    const viewportManager = require('oroui/js/viewport-manager').default;
    const _ = require('underscore');
    const $ = require('jquery');

    const FrontendDialogWidget = DialogWidget.extend({
        /**
         * @inheritdoc
         */
        optionNames: DialogWidget.prototype.optionNames.concat([
            'fullscreenViewport', 'fullscreenDialogOptions',
            'fullscreenMode', 'actionsTemplate', 'simpleActionTemplate',
            'contentElement', 'renderActionsFromTemplate', 'staticPage',
            'excludeClasses'
        ]),

        /**
         * @property {string}
         */
        actionsTemplate: actionsTemplate,

        /**
         * @property {boolean}
         */
        simpleActionTemplate: false,

        /**
         * @property {string}
         */
        contentElement: 'section.page-content',

        /**
         * List of classes which will be removed from "contentElement"
         *
         * @property {string}
         */
        excludeClasses: 'page-content--has-sidebar page-content--has-sidebar-right',

        /**
         * @property {boolean}
         */
        renderActionsFromTemplate: false,

        /**
         * @property {Object}
         */
        fullscreenViewport: 'tablet-small',


        /**
         * Default options of fullscreen dialog for correct render
         * @property {Object}
         */
        fullscreenDialogOptionsDefaults: {
            modal: true,
            autoResize: false,
            resizable: false,
            draggable: false,
            width: '100%',
            minWidth: '100%',
            maxWidth: '100%',
            height: 'auto',
            maxHeight: 'auto',
            minHeight: 'auto',
            position: null,
            closeOnDialogTitle: true
        },

        /**
         * @property {boolean}
         */
        fullscreenMode: true,

        /**
         * Property detect needed viewport of device
         * @property {boolean}
         */
        isApplicable: false,

        /**
         * @property {jQuery.Element}
         */
        $header: null,

        /**
         * @property {boolean}
         */
        rendered: false,

        /**
         * @property {boolean}
         */
        useDialog: false,

        /**
         * @property {boolean}
         */
        staticPage: false,

        listen: {
            adoptedFormSubmitClick: 'resetDialogPosition'
        },

        /**
         * @inheritdoc
         */
        constructor: function FrontendDialogWidget(options) {
            FrontendDialogWidget.__super__.constructor.call(this, options);
        },

        /**
         * @inheritdoc
         */
        initialize: function(options) {
            FrontendDialogWidget.__super__.initialize.call(this, options);
            this._processFullscreenOptions();
            this.options.dialogOptions = _.defaults(this.options.dialogOptions, {
                close: this._onClose.bind(this)
            });
        },

        /**
         * Apply full screen options
         * @private
         */
        _processFullscreenOptions() {
            this.isApplicable = viewportManager.isApplicable(this.fullscreenViewport);

            if (this.isApplicable && this.fullscreenMode) {
                this.options.dialogOptions = {
                    ...this.options.dialogOptions,
                    ...this.fullscreenDialogOptionsDefaults,
                    ...this.fullscreenDialogOptions || {},
                    ...this.options.fullscreenDialogOptions || {}
                };

                const {title} = this.options.dialogOptions;

                if (title === void 0 || (typeof title === 'string' && title.trim().length === 0)) {
                    this.options.dialogOptions.title = _.__('Back');
                }
                // Dialog occupies a whole screen, so not necessary to position it
                this.options.incrementalPosition = false;
            }
        },

        /**
         * @inheritdoc
         */
        internalSetDialogPosition(position, leftShift, topShift) {
            if (!this.widget) {
                throw new Error('this function must be called only after dialog is created');
            }

            const $uiDialog = this.widget.dialog('instance').uiDialog;

            if (!$uiDialog.hasClass('fullscreen')) {
                FrontendDialogWidget.__super__.internalSetDialogPosition.call(this, position, leftShift, topShift);
            }
        },

        /**
         * @inheritdoc
         */
        show: function(options) {
            FrontendDialogWidget.__super__.show.call(this, options);
            if (this.isApplicable && this.fullscreenMode) {
                this.showFullscreen();
            }
        },

        /**
         * Listen widget create event
         * @param content
         */
        onWidgetRender: function(content) {
            FrontendDialogWidget.__super__.onWidgetRender.call(this, content);
            this._setHeader();
        },

        /**
         * Apply dialog header
         *
         * @private
         */
        _setHeader: function() {
            if (this.options.header) {
                const $title = this.widget.dialog('instance').uiDialogTitlebar;
                if (this.$header) {
                    this.$header.remove();
                }
                this.$header = $(this.options.header).prependTo($title);
            }
        },

        /**
         * Tunes dialog to occupy a whole screen
         */
        showFullscreen: function() {
            const $uiDialog = this.widget.dialog('instance').uiDialog;

            // Disable JS positioning
            $uiDialog._position = $.noop;
            $uiDialog.addClass('fullscreen');
        },

        /**
         * Filter send data via dialog open request
         *
         * @param data
         * @param method
         * @param url
         * @returns {*}
         */
        prepareContentRequestOptions: function(data, method, url) {
            const options = FrontendDialogWidget.__super__.prepareContentRequestOptions.call(this, data, method, url);

            if (this.staticPage) {
                options.data = '';
            }

            return options;
        },

        /**
         * Filtered on loaded content
         *
         * @param content
         * @returns {*}
         * @private
         */
        _onContentLoad: function(content) {
            if (this.renderActionsFromTemplate) {
                content = $(content).find(this.contentElement);

                content
                    .removeClass(this.excludeClasses)
                    .addClass('widget-content')
                    .append(this.actionsTemplate({
                        simpleActionTemplate: this.simpleActionTemplate
                    }));

                content = content.parent().html();
            }

            return FrontendDialogWidget.__super__._onContentLoad.call(this, content);
        },

        /**
         * Handled submit dialog method
         *
         * @param form
         * @returns {*}
         * @private
         */
        _onAdoptedFormSubmitClick: function(form, widget) {
            this.trigger('accept');
            if (form) {
                return FrontendDialogWidget.__super__._onAdoptedFormSubmitClick.call(this, form, widget);
            }

            this.widget.dialog('close');
        },

        /**
         * Handled reset dialog method
         *
         * @param form
         * @returns {*}
         * @private
         */
        _onAdoptedFormResetClick: function(form) {
            this.trigger('cancel');
            if (form) {
                return FrontendDialogWidget.__super__._onAdoptedFormResetClick.call(this, form);
            }

            this.widget.dialog('close');
        },

        /**
         * Default on close handler
         *
         * @private
         */
        _onClose: function() {
            this.trigger('close');
        },

        /**
         * Hide dialog
         */
        hide: function() {
            FrontendDialogWidget.__super__.hide.call(this);
        },

        /**
         * Override parent method
         */
        getActionsElement: function() {
            if (!this.actionsEl) {
                this.actionsEl = $('<div class="form-actions widget-actions"/>').appendTo(
                    this.widget.dialog('actionsContainer')
                );
            }
            return this.actionsEl;
        }
    });

    return FrontendDialogWidget;
});

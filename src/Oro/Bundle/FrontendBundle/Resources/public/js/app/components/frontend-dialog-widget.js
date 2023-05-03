define(function(require) {
    'use strict';

    const DialogWidget = require('oro/dialog-widget');
    const FullScreenPopupView = require('orofrontend/default/js/app/views/fullscreen-popup-view');
    const actionsTemplate = require('tpl-loader!orofrontend/templates/frontend-dialog/dialog-actions.html');
    const viewportManager = require('oroui/js/viewport-manager').default;
    const _ = require('underscore');
    const $ = require('jquery');

    const FrontendDialogWidget = DialogWidget.extend({
        /**
         * @inheritdoc
         */
        optionNames: DialogWidget.prototype.optionNames.concat([
            'fullscreenViewport', 'fullscreenViewOptions', 'fullscreenDialogOptions',
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
         * @property {Object}
         */
        fullscreenViewOptions: {},

        /**
         * Default options of fullscreen dialog for correct render
         * @property {Object}
         */
        fullscreenDialogOptions: {
            modal: false,
            title: null,
            autoResize: false,
            resizable: false,
            draggable: false,
            width: 'auto',
            incrementalPosition: false,
            position: null
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
            this.isApplicable = viewportManager.isApplicable(this.fullscreenViewport);

            this.options.dialogOptions = _.defaults(this.options.dialogOptions, {
                close: this._onClose.bind(this)
            });

            if (this.isApplicable && this.fullscreenMode) {
                this.options.dialogOptions = _.extend({}, this.options.dialogOptions, this.fullscreenDialogOptions);
            }
        },

        /**
         * @inheritdoc
         */
        isEmbedded: function() {
            if (this.fullscreenMode) {
                return true;
            }

            return FrontendDialogWidget.__super__.isEmbedded.call(this);
        },

        /**
         * @inheritdoc
         */
        dispose: function() {
            if (this.disposed) {
                return;
            }
            this.disposeProcess = true;
            return FrontendDialogWidget.__super__.dispose.call(this);
        },

        /**
         * @inheritdoc
         */
        show: function(options) {
            FrontendDialogWidget.__super__.show.call(this, options);
            if (this.isApplicable && !this.rendered && this.fullscreenMode) {
                this.rendered = true;
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
         * Create and show fullscreen popup via fullscreen mode
         */
        showFullscreen: function() {
            if (this.$header) {
                this.fullscreenViewOptions.headerElement = this.$header;
                this.fullscreenViewOptions.headerTemplate = null;
            }

            this.fullscreenViewOptions.contentElement = this.widget.dialog('instance').uiDialog.get(0);
            this.toggleFullscreenDialogClass();

            this.subview('fullscreenView', new FullScreenPopupView(this.fullscreenViewOptions));
            this.subview('fullscreenView').show();
            this.subview('fullscreenView').on('close', function() {
                if (!this.disposeProcess) {
                    this.remove();
                }
            }, this);

            this.renderActionsContainer();
        },

        /**
         * Extend main dialog class for fullscreen mode
         */
        toggleFullscreenDialogClass: function(state = true) {
            const $uiDialog = this.widget.dialog('instance').uiDialog;
            const uiDialogClass = this.options.dialogOptions.dialogClass + '-fullscreen';
            $uiDialog.toggleClass(uiDialogClass, state);
        },

        /**
         * Render actions button into footer
         */
        renderActionsContainer: function() {
            const fullscreen = this.subview('fullscreenView');
            if (fullscreen && !fullscreen.disposed) {
                fullscreen.footer.Element = this.getActionsElement();
                fullscreen.footer.attr = {'class': 'fullscreen-popup__actions-wrapper'};
                fullscreen.showSection('footer');
            }
        },

        /**
         * @inheritdoc
         */
        resetDialogPosition: function() {
            if (!this.subview('fullscreenView')) {
                return FrontendDialogWidget.__super__.resetDialogPosition.call(this);
            }
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
            this.trigger('frontend-dialog:accept');
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
            this.trigger('frontend-dialog:cancel');
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
            this.trigger('frontend-dialog:close');
        },

        /**
         * Hide dialog
         */
        hide: function() {
            FrontendDialogWidget.__super__.hide.call(this);

            const fullscreen = this.subview('fullscreenView');
            this.toggleFullscreenDialogClass(false);

            if (fullscreen && !fullscreen.disposed) {
                fullscreen.trigger('close');
            }
        }
    });

    return FrontendDialogWidget;
});

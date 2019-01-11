define(function(require) {
    'use strict';

    var FrontendDialogWidget;
    var DialogWidget = require('oro/dialog-widget');
    var FullScreenPopupView = require('orofrontend/blank/js/app/views/fullscreen-popup-view');
    var ViewportManager = require('oroui/js/viewport-manager');
    var actionsTemplate = require('tpl!orofrontend/templates/frontend-dialog/dialog-actions.html');
    var _ = require('underscore');
    var $ = require('jquery');

    FrontendDialogWidget = DialogWidget.extend({
        /**
         * @inheritDoc
         */
        optionNames: DialogWidget.prototype.optionNames.concat([
            'fullscreenViewport', 'fullscreenViewOptions', 'fullscreenDialogOptions',
            'fullscreenMode', 'actionsTemplate', 'simpleActionTemplate',
            'contentElement', 'renderActionsFromTemplate', 'staticPage'
        ]),

        /**
         * @property {String}
         */
        actionsTemplate: actionsTemplate,

        /**
         * @property {Boolean}
         */
        simpleActionTemplate: false,

        /**
         * @property {String}
         */
        contentElement: 'section.page-content',

        /**
         * @property {Boolean}
         */
        renderActionsFromTemplate: false,

        /**
         * @property {Object}
         */
        fullscreenViewport: {
            isMobile: true
        },

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
         * @property {Boolean}
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
         * @property {Boolean}
         */
        useDialog: false,

        /**
         * @property {Boolean}
         */
        staticPage: false,

        /**
         * @inheritDoc
         */
        constructor: function FrontendDialogWidget() {
            FrontendDialogWidget.__super__.constructor.apply(this, arguments);
        },

        /**
         * @inheritDoc
         */
        initialize: function(options) {
            FrontendDialogWidget.__super__.initialize.call(this, options);
            this.isApplicable = this.fullscreenViewport
                ? ViewportManager.isApplicable(this.fullscreenViewport) : null;

            this.options.dialogOptions = _.defaults(this.options.dialogOptions, {
                close: _.bind(this._onClose, this)
            });

            if (this.isApplicable && this.fullscreenMode) {
                this.setFullscreenDialogClass();
                this.options.dialogOptions = _.extend({}, this.options.dialogOptions, this.fullscreenDialogOptions);
            }
        },

        /**
         * @inheritDoc
         */
        dispose: function() {
            if (this.disposed) {
                return;
            }
            this.disposeProcess = true;
            return FrontendDialogWidget.__super__.dispose.call(this);
        },

        /**
         * @inheritDoc
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
                var $title = this.widget.dialog('instance').uiDialogTitlebar;
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
        setFullscreenDialogClass: function() {
            this.fullscreenDialogOptions.dialogClass = this.options.dialogOptions.dialogClass + '-fullscreen';
        },

        /**
         * Render actions button into footer
         */
        renderActionsContainer: function() {
            var fullscreen = this.subview('fullscreenView');
            if (fullscreen) {
                fullscreen.footer.Element = this.getActionsElement();
                fullscreen.footer.attr = {'class': 'fullscreen-popup__actions-wrapper'};
                fullscreen.showSection('footer');
            }
        },

        /**
         * @inheritDoc
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
            var options = FrontendDialogWidget.__super__.prepareContentRequestOptions.apply(this, arguments);

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
                content = $(content).find(this.contentElement).addClass('widget-content');

                content.append(this.actionsTemplate({
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
        _onAdoptedFormSubmitClick: function(form) {
            this.trigger('frontend-dialog:accept');
            if (form) {
                return FrontendDialogWidget.__super__._onAdoptedFormSubmitClick.apply(this, arguments);
            }

            this.dispose();
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
                return FrontendDialogWidget.__super__._onAdoptedFormResetClick.apply(this, arguments);
            }

            this.dispose();
        },

        /**
         * Default on close handler
         *
         * @private
         */
        _onClose: function() {
            this.trigger('frontend-dialog:close');
        }
    });

    return FrontendDialogWidget;
});

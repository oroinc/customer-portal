define(function(require) {
    'use strict';

    var FrontendDialogWidget;
    var DialogWidget = require('oro/dialog-widget');
    var FullScreenPopupView = require('orofrontend/blank/js/app/views/fullscreen-popup-view');
    var ViewportManager = require('oroui/js/viewport-manager');
    var _ = require('underscore');
    var $ = require('jquery');

    FrontendDialogWidget = DialogWidget.extend({

        optionNames: DialogWidget.prototype.optionNames.concat([
            'fullscreenViewport', 'fullscreenViewOptions', 'fullscreenDialogOptions'
        ]),

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
         * Property detect needed viewport of device
         * @property {boolean}
         */
        isApplicable: false,

        $header: null,

        /**
         * @inheritDoc
         */
        initialize: function(options) {
            FrontendDialogWidget.__super__.initialize.call(this, options);
            this.isApplicable = this.fullscreenViewport ?
                ViewportManager.isApplicable(this.fullscreenViewport) : null;

            if (this.isApplicable) {
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
            if (this.isApplicable) {
                this.showFullscreen();
            }
        },

        onWidgetRender: function(content) {
            FrontendDialogWidget.__super__.onWidgetRender.call(this, content);
            this._setHeader();
        },

        _setHeader: function() {
            if (this.options.header) {
                var $title = this.widget.dialog('instance').uiDialogTitlebar;
                if (this.$header) {
                    this.$header.remove();
                }
                this.$header = $(this.options.header).prependTo($title);
            }
        },

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
        }
    });

    return FrontendDialogWidget;
});

define(function(require) {
    'use strict';

    var FrontendDialogWidget;
    var DialogWidget = require('oro/dialog-widget');
    var FullScreenPopupView = require('orofrontend/blank/js/app/views/fullscreen-popup-view');
    var ViewportManager = require('oroui/js/viewport-manager');
    var _ = require('underscore');

    FrontendDialogWidget = DialogWidget.extend({

        optionNames: DialogWidget.prototype.optionNames.concat([
            'fullscreenViewport', 'fullscreenViewOptions', 'fullscreenDialogOptions'
        ]),

        /**
         * @property {Object}
         */
        fullscreenViewport: null,

        /**
         * @property {Object}
         */
        fullscreenViewOptions: {
            footerContentOptions: {}
        },

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

        /**
         * @inheritDoc
         */
        initialize: function(options) {
            FrontendDialogWidget.__super__.initialize.call(this, options);
            this.isApplicable = this.options.fullscreenViewport ?
                ViewportManager.isApplicable(this.options.fullscreenViewport) : null;

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
                this.fullscreenViewOptions.contentElement = this.widget.dialog('instance').uiDialog.get(0);

                this.subview('fullscreenView', new FullScreenPopupView(this.fullscreenViewOptions));
                this.subview('fullscreenView').show();
                this.subview('fullscreenView').on('close', function() {
                    if (!this.disposeProcess) {
                        this.remove();
                    }
                }, this);

                this.renderActionsContainer();
            }
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
            if (this.subview('fullscreenView')) {
                var $actions = this.getActionsElement();
                $actions.attr('class', 'fullscreen-popup__actions-wrapper');
                this.subview('fullscreenView').renderPopupFooterContent($actions);
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

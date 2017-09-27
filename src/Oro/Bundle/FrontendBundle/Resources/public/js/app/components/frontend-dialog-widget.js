define(function(require) {
    'use strict';

    var FrontendDialogWidget;
    var DialogWidget = require('oro/dialog-widget');
    var FullScreenPopupView = require('orofrontend/blank/js/app/views/fullscreen-popup-view');
    var ViewportManager = require('oroui/js/viewport-manager');

    /**
     * @export  oro/orofrontend/js/app/components/frontend-dialog-widget
     * @class   oro.FrontendDialogWidget
     * @extends oroui.widget.FrontendDialogWidget
     */
    FrontendDialogWidget = DialogWidget.extend({

        optionNames: DialogWidget.prototype.optionNames.concat([
            'fullscreenView', 'fullscreenViewOptions', 'fullscreenDialogOptions'
        ]),

        /**
         * @property {Object}
         */
        fullscreenView: null,

        /**
         * @property {Object}
         */
        fullscreenViewport: null,

        /**
         * @property {Object}
         */
        fullscreenViewOptions: {
            popupBadge: true,
            popupIcon: 'fa-gift',
            popupLabel: 'product',
            footerContent: true,
            keepAliveOnClose: false
        },

        /**
         * Default options of fullscreen dialog for correct render
         * @property {Object}
         */
        fullscreenDialogOptions: {
            'appendTo': '[data-role="content"]',
            'modal': false,
            'title': null,
            'autoResize': false,
            'resizable': false,
            'width': 'auto'
        },

        /**
         * Property detect needed viewport of device
         * @property {boolean}
         */
        isApplicable: false,

        /**
         * @param {Object} options
         * @override
         */
        initialize: function(options) {
            FrontendDialogWidget.__super__.initialize.call(this, options);
            this.isApplicable = this.options.fullscreenViewport ?
                ViewportManager.isApplicable(this.options.fullscreenViewport) : null;

            if (this.isApplicable) {
                this.setFullscreenDialogClass();
                this.options.dialogOptions = this.fullscreenDialogOptions;
            }
        },

        /**
         * Show dialog
         *
         * @param {Object} options
         * @override
         */
        show: function(options) {
            if (this.isApplicable) {
                this.fullscreenView = new FullScreenPopupView(this.fullscreenViewOptions);
                this.fullscreenView.show();
                this.bindEvents();
            }
            FrontendDialogWidget.__super__.show.call(this, options);
            if (this.isApplicable) {
                this.renderActionsContainer();
            }
        },

        /**
         * Binding events of dialog and fullscreen views
         */
        bindEvents: function() {
            this.fullscreenView.on('beforeClose', this.dispose, this);
        },

        /**
         * Extand main dialog class for fullscreen mode
         */
        setFullscreenDialogClass: function() {
            this.fullscreenDialogOptions.dialogClass = this.options.dialogOptions.dialogClass + '-fullscreen';
        },

        /**
         * Render actions button into footer
         */
        renderActionsContainer: function() {
            this.fullscreenView.renderPopupFooterContent(this.getActionsElement('fullscreen-popup__actions-wrapper'));
        }
    });

    return FrontendDialogWidget;
});

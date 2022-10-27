define(function(require) {
    'use strict';

    /**
     * This component disable zoom ability on mobile devices when user tap on input, textarea, select etc.
     * If you want to bind disable zoom component for particular element just set attr: [data-zoom-disable]
     */
    const _ = require('underscore');
    const $ = require('jquery');

    const ZoomDisable = {
        /**
         * @property {Object}
         */
        $elements: {
            head: 'head',
            viewPort: 'head meta[name=viewport]',
            targetElements: [
                '[data-zoom-disable]',
                'input',
                'textarea',
                'select',
                'label[for]',
                '.select2-result',
                '.select2-container',
                '.datagrid-settings.dropdown'
            ].join(', ') // Target DOM elements to disable zoom
        },

        /**
         * @property {Object}
         */
        options: {
            metaEnableZoomContent: 'width=device-width, initial-scale=1, viewport-fit=cover',
            metaDisableZoomContent: 'width=device-width, initial-scale=1, viewport-fit=cover, user-scalable=0'
        },

        metaEnableZoom: '',
        metaDisableZoom: '',

        /**
         * @constructor
         */
        initialize: function() {
            this.metaEnableZoom = '<meta name="viewport" content="' + this.options.metaEnableZoomContent + '" />';
            this.metaDisableZoom = '<meta name="viewport" content="' + this.options.metaDisableZoomContent + '" />';
            this.bindEvents();
        },

        /**
         * Bind touchstart and touchend events
         */
        bindEvents: function() {
            const self = this;

            if (_.isMobile()) {
                $(document).on({
                    touchstart: function() {
                        self.toggleZoom(false);
                    },
                    touchend: function() {
                        self.toggleZoom(true);
                    },
                    // Disable zoom when user switch active input by 'tab' button (arrows on iOS)
                    blur: function() {
                        self.toggleZoom();
                    }
                }, this.$elements.targetElements);
            }
        },

        /**
         * Change viewport meta tag
         *
         * @param {string} viewPort
         */
        changeViewport: function(viewPort) {
            $(this.$elements.viewPort).remove();
            $(this.$elements.head).prepend(viewPort);
        },

        /**
         * Toggle zoom on page
         *
         * @param {boolean} state
         */
        toggleZoom: function(state) {
            const enableZoom = this.metaEnableZoom;
            const disableZoom = this.metaDisableZoom;

            if (_.isUndefined(state)) {
                this.changeViewport(disableZoom);
                // Timeout needed for correct behavior of select2
                this.changeViewportDebounce(enableZoom);
                return;
            }

            if (state) {
                // Timeout needed for correct behavior of select2
                this.changeViewportDebounce(enableZoom);
            } else {
                this.changeViewport(disableZoom);
            }
        },

        /**
         * Debounce wrapper for 'changeViewport' method with delay
         *
         * @param {string} viewPort
         */
        changeViewportDebounce: _.debounce(function(viewPort) {
            this.changeViewport(viewPort);
        }, 500)
    };

    return ZoomDisable.initialize();
});

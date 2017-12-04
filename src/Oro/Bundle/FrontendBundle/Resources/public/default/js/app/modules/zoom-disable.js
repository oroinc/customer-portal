define(function(require) {
    'use strict';

    /**
     * This component disable zoom ability on mobile devices when user tap on input, textarea, select etc.
     * If you want to bind disable zoom component for particular element just set attr: [data-zoom-disable]
     */
    var ZoomDisable;
    var _ = require('underscore');
    var tools = require('oroui/js/tools');
    var $ = require('jquery');

    ZoomDisable = {
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
                '.column-manager.dropdown'
            ].join(', ') //Target DOM elements to disable zoom
        },

        /**
         * @property {Object}
         */
        options: {
            metaEnableZoom: '<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />',
            metaDisableZoom: '<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover, user-scalable=0" />'
        },

        /**
         * @constructor
         */
        initialize: function() {
            this.bindEvents();
        },

        /**
         * Bind touchstart and touchend events
         */
        bindEvents: function() {
            var self = this;

            if (tools.isMobile()) {
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
            var enableZoom = this.options.metaEnableZoom;
            var disableZoom = this.options.metaDisableZoom;

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

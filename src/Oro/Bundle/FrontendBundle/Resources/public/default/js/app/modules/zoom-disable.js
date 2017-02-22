define(function(require) {
    'use strict';

    /**
     * This component disable zoom ability on mobile devices when user tap on input, textarea, select etc.
     * If you want to bind disable zoom component for particular element just set attr: [data-zoom-disable]
     */
    var ZoomDisable;
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
            metaEnableZoom: '<meta name="viewport" content="width=device-width, initial-scale=1" />',
            metaDisableZoom: '<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=0" />'
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
            var elements = this.$elements.targetElements;
            var enableZoom = this.options.metaEnableZoom;
            var disableZoom = this.options.metaDisableZoom;

            if (tools.isMobile()) {
                $(document.body).on('touchstart', elements, function() {
                    self.changeViewport(disableZoom);
                }).on('touchend', elements, function() {
                    setTimeout(function() {
                        self.changeViewport(enableZoom);
                    }, 500); //Timeout needed for correct behavior of select2
                });
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
        }
    };

    return ZoomDisable.initialize();
});

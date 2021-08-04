define(function(require) {
    'use strict';

    const BaseView = require('oroui/js/app/views/base/view');
    const _ = require('underscore');

    const FitMatrixView = BaseView.extend({
        /**
         * @property {jQuery}
         */
        $matrixContainer: null,

        /**
         * @property {jQuery}
         */
        $scrollView: null,

        listen: {
            'layout:reposition mediator': 'fitMatrix'
        },

        states: ['state-labels-above', 'state-multiline'],

        /**
         * @inheritdoc
         */
        constructor: function FitMatrixView(options) {
            FitMatrixView.__super__.constructor.call(this, options);
        },

        /**
         * @inheritdoc
         */
        initialize: function(options) {
            FitMatrixView.__super__.initialize.call(this, options);

            this.$matrixContainer = this.$('[data-matrix-grid-container]');
            this.$scrollView = this.$('[data-scroll-view]');

            this.fitMatrix();

            this.fitMatrix = _.debounce(this.fitMatrix, 100).bind(this);
        },

        /**
         * Check breakpoints with defined states
         */
        fitMatrix: function() {
            this.resetState();

            for (let i = 0; i < this.states.length; i++) {
                if (!this.isFittedContainer()) {
                    return;
                }

                this.resetState();
                this.$matrixContainer.addClass(this.states[i]);
            }
        },

        /**
         * Check is matrix container fit on screen
         *
         * @returns {boolean}
         */
        isFittedContainer: function() {
            const scrollView = this.$scrollView.get(0);

            return scrollView.clientWidth < scrollView.scrollWidth;
        },

        /**
         * Reset matrix view state
         */
        resetState: function() {
            this.$matrixContainer.removeClass(this.states.join(' '));
        },

        dispose: function() {
            if (this.disposed) {
                return;
            }

            delete this.$matrixContainer;
            delete this.$row;

            FitMatrixView.__super__.dispose.call(this);
        }
    });

    return FitMatrixView;
});

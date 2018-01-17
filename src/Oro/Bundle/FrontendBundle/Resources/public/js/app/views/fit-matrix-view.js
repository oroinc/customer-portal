define(function(require) {
    'use strict';

    var FitMatrixView;
    var BaseView = require('oroui/js/app/views/base/view');
    var _ = require('underscore');

    FitMatrixView = BaseView.extend({
        /**
         * @property {jQuery}
         */
        $matrixContainer: null,

        /**
         * @property {jQuery}
         */
        $row: null,

        listen: {
            'layout:reposition mediator': 'fitMatrix'
        },

        states: ['state-labels-above', 'state-multiline'],

        initialize: function(options) {
            FitMatrixView.__super__.initialize.apply(this, arguments);

            this.$matrixContainer = this.$('[data-matrix-grid-container]');
            this.$row = this.$('[data-row]');

            this.fitMatrix();

            this.fitMatrix = _.debounce(this.fitMatrix, 100).bind(this);
        },

        /**
         * Check breakpoints with defined states
         */
        fitMatrix: function() {
            this.resetState();

            for (var i = 0; i < this.states.length; i++) {
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
            return this.$matrixContainer.width() < this.$row.outerWidth();
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

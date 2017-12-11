define(function(require) {
    'use strict';

    var FitMatrixView;
    var BaseView = require('oroui/js/app/views/base/view');
    var ElementsHelper = require('orofrontend/js/app/elements-helper');
    var _ = require('underscore');

    FitMatrixView = BaseView.extend(_.extend({}, ElementsHelper, {
        elements: {
            columns: '[data-column]',
            matrixContainer: '[data-matrix-grid-container]'
        },

        listen: {
            'layout:reposition mediator': 'fitMatrix'
        },

        states: ['state-labels-above', 'state-multiline'],

        initialize: function(options) {
            FitMatrixView.__super__.initialize.apply(this, arguments);
            this.initializeElements(options);
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
                this.getElement('matrixContainer').addClass(this.states[i]);
            }
        },

        /**
         * Check is matrix container fit on screen
         *
         * @returns {boolean}
         */
        isFittedContainer: function() {
            var itemWidth = this.getElement('columns').outerWidth();
            var itemWidthSum = itemWidth * this.getElement('columns').length;

            return this.getElement('matrixContainer').width() < itemWidthSum;
        },

        /**
         * Reset matrix view state
         */
        resetState: function() {
            this.getElement('matrixContainer').removeClass(this.states.join(' '));
        }
    }));

    return FitMatrixView;
});

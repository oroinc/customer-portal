define(function(require, exports, module) {
    'use strict';

    const _ = require('underscore');
    const AbstractFilter = require('oro/filter/abstract-filter');

    let config = require('module-config').default(module.id);

    config = _.extend({
        animationDuration: 0
    }, config);

    const FrontendAbstractFilter = AbstractFilter.extend({
        /**
         * Duration of slide up/down filter criteria
         *
         * @property {Number}
         */
        animationDuration: config.animationDuration,

        /**
         * @inheritdoc
         */
        constructor: function FrontendAbstractFilter(options) {
            FrontendAbstractFilter.__super__.constructor.call(this, options);
        },

        /**
         * Set filter button class
         *
         * @param {Object} element
         * @param {Boolean} status
         * @protected
         */
        _setButtonPressed: function(element, status) {
            if (!this.animationDuration) {
                return FrontendAbstractFilter.__super__._setButtonPressed.call(this, element, status);
            }

            if (status) {
                element.slideDown(this.animationDuration, () => {
                    this._setButtonExpanded(true);
                    element.parent().addClass(this.buttonActiveClass);
                });
            } else {
                element.slideUp(this.animationDuration, () => {
                    this._setButtonExpanded(false);
                    element.parent().removeClass(this.buttonActiveClass);
                });
            }
        }
    });

    return FrontendAbstractFilter;
});

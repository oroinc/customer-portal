define(function(require) {
    'use strict';

    var _ = require('underscore');
    var __ = require('orotranslation/js/translator');
    var SelectFilter = require('oro/filter/select-filter');

    _.extend(SelectFilter.prototype, {

        /**
         * Set container for dropdown
         * @return {jQuery}
         */
        _setDropdownContainer: function() {
            var $container = null;

            if (_.isMobile()) {
                $container =  this.$el.find('.filter-criteria');
            } else {
                $container =  this.dropdownContainer;
            }

            return $container;
        }
    });
});

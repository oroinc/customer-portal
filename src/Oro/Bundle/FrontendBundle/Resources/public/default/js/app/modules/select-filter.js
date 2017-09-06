define(function(require) {
    'use strict';

    var _ = require('underscore');
    var SelectFilter = require('oro/filter/select-filter');
    var MultiselectDecorator = require('orofrontend/js/app/datafilter/frontend-multiselect-decorator');

    _.extend(SelectFilter.prototype, {
        /**
         * @property
         */
        MultiselectDecorator: MultiselectDecorator,

        /**
         * Select widget options
         *
         * @property
         */
        widgetOptions: {
            multiple: false,
            classes: 'select-filter-widget'
        }
    });
});

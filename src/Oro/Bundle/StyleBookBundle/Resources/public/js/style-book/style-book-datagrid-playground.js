define(function(require) {
    'use strict';

    const StyleBookPlayground = require('orostylebook/js/style-book/style-book-playground');
    const datagridData = require('orofrontend/json/grid-config');
    const _ = require('underscore');

    const StyleBookDatagridPlayground = StyleBookPlayground.extend({
        subviewContainer: '[data-example-view]',
        /**
         * @inheritdoc
         */
        constructor: function StyleBookDatagridPlayground(options) {
            return StyleBookDatagridPlayground.__super__.constructor.call(this, options);
        },

        initialize: function(options) {
            this.viewOptions = _.extend({}, this.viewOptions, datagridData);
            StyleBookDatagridPlayground.__super__.initialize.call(this, options);
        },

        createView: function(View) {
            this.viewConstructor = View;
            this.constructorName = View.name;

            if (this.$el.find(this.subviewContainer).length) {
                _.extend(this.viewOptions, {
                    _sourceElement: this.$el.find(this.subviewContainer),
                    el: this.$el.find(this.subviewContainer).get()
                });
            }

            this.subview(this.constructorName, new View(this.viewOptions));
        },

        updateConfigPreview: function() {
            this.configPreview.text(JSON.stringify(_.omit(this.viewOptions, ['data', 'massActions']), null, '\t'));
        }
    });

    return StyleBookDatagridPlayground;
});

define(function(require) {
    'use strict';

    var StyleBookDatagridPlayground;
    var StyleBookPlayground = require('orofrontend/js/app/views/style-book-playground');
    var datagridData = require('orofrontend/json/grid-config');

    StyleBookDatagridPlayground = StyleBookPlayground.extend({
        subviewContainer: '[data-example-view]',
        /**
         * @inheritDoc
         */
        constructor: function StyleBookDatagridPlayground() {
            return StyleBookDatagridPlayground.__super__.constructor.apply(this, arguments);
        },

        initialize: function(options) {
            this.viewOptions = _.extend({}, this.viewOptions, datagridData);
            StyleBookDatagridPlayground.__super__.initialize.apply(this, arguments);
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

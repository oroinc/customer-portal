define(function(require) {
    'use strict';

    const BaseView = require('oroui/js/app/views/base/view');
    const _ = require('underscore');

    /**
     * Title view, able to handle title rendering.
     *
     * Usage sample:
     * ```javascript
     * const titleView = new TitleView({
     *     model: new Backbone.Model({
     *         title: "Title text"
     *     }),
     *     fieldName: 'title',
     *     autoRender: true
     * });
     * ```
     *
     * @class
     * @augments BaseView
     * @exports TitleView
     */
    const TitleView = BaseView.extend(/** @lends TitleView.prototype */{
        template: require('tpl-loader!orofrontend/templates/viewer/title-view.html'),

        listen: {
            'change model': 'render'
        },

        /**
         * @inheritdoc
         */
        constructor: function TitleView(options) {
            TitleView.__super__.constructor.call(this, options);
        },

        /**
         * @inheritdoc
         */
        initialize: function(options) {
            this.fieldName = _.result(options, 'fieldName', 'value');
            this.tooltip = _.result(options, 'tooltip', null);
            this.additionalClass = _.result(options, 'additionalClass', '');
            return TitleView.__super__.initialize.call(this, options);
        },

        getTemplateData: function() {
            return {
                value: _.escape(this.model.get(this.fieldName)),
                tooltip: this.tooltip,
                additionalClass: this.additionalClass
            };
        }
    });

    return TitleView;
});

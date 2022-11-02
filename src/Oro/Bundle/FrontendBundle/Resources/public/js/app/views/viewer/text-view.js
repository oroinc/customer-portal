define(function(require) {
    'use strict';
    const BaseView = require('oroui/js/app/views/base/view');
    const _ = require('underscore');

    /**
     * Text view, able to handle title rendering.
     *
     * Usage sample:
     * ```javascript
     * const textView = new TextView({
     *     model: new Backbone.Model({
     *         note: "Some text"
     *     }),
     *     fieldName: 'note',
     *     autoRender: true
     * });
     * ```
     *
     * @class
     * @augments BaseView
     * @exports TextView
     */
    const TextView = BaseView.extend(/** @lends TextView.prototype */{
        template: require('tpl-loader!orofrontend/templates/viewer/text-view.html'),

        listen: {
            'change model': 'render'
        },

        /**
         * @inheritdoc
         */
        constructor: function TextView(options) {
            TextView.__super__.constructor.call(this, options);
        },

        /**
         * @inheritdoc
         */
        initialize: function(options) {
            this.fieldName = _.result(options, 'fieldName', 'value');
            return TextView.__super__.initialize.call(this, options);
        },

        getTemplateData: function() {
            return {
                value: this.model.get(this.fieldName)
            };
        }
    });

    return TextView;
});

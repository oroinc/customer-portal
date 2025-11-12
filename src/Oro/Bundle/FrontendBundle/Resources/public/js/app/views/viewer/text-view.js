import BaseView from 'oroui/js/app/views/base/view';
import _ from 'underscore';
import template from 'tpl-loader!orofrontend/templates/viewer/text-view.html';

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
    template,

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

export default TextView;

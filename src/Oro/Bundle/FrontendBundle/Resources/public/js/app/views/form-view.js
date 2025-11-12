import BaseView from 'oroui/js/app/views/base/view';
import mediator from 'oroui/js/mediator';

const FormView = BaseView.extend({
    optionNames: BaseView.prototype.optionNames.concat(['selectors']),

    events: function() {
        const {selectors = {}} = this;
        const eventsEntries = Object.entries(selectors)
            .map(([selector, key]) => [
                `change ${selector}`,
                e => mediator.trigger(`update:${key}`, this.$(e.target).val())
            ]);
        return Object.fromEntries(eventsEntries);
    },

    /**
     * @inheritdoc
     */
    constructor: function FormView(options) {
        FormView.__super__.constructor.call(this, options);
    }
});

export default FormView;

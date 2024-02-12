import $ from 'jquery';
import _ from 'underscore';
import __ from 'orotranslation/js/translator';
import NumberFormatter from 'orolocale/js/formatter/number';
import IncrementButtonView from 'orofrontend/default/js/app/views/increment-input/increment-button-view';

import BaseView from 'oroui/js/app/views/base/view';

const IncrementInputView = BaseView.extend({
    /**
     * @inheritdoc
     */
    autoRender: true,

    /**
     * @inheritdoc
     */
    noWrap: true,

    /**
     * Allowed minimum value to be set.
     */
    min: null,

    /**
     * Allowed maximum value to be set.
     */
    max: null,

    /**
     * Increases / decreases step
     */
    step: null,

    /**
     * @inheritdoc
     */
    events: {
        'click [data-type="decrement"]': 'doDecrementValue',
        'click [data-type="increment"]': 'doIncrementValue'
    },

    /**
     * @inheritdoc
     */
    constructor: function IncrementInputView(options) {
        IncrementInputView.__super__.constructor.call(this, options);
    },

    /**
     * @inheritdoc
     */
    initialize(options) {
        IncrementInputView.__super__.initialize.call(this, options);

        this.subview('minus', new IncrementButtonView({
            dataType: 'decrement',
            extraClass: 'input-quantity-btn--minus',
            ariaLabel: __('oro_frontend.input_widget.step_input.decrease'),
            noWrap: true,
            icon: 'minus'
        }));

        this.subview('plus', new IncrementButtonView({
            container: this.$el.parent(),
            dataType: 'increment',
            extraClass: 'input-quantity-btn--plus',
            ariaLabel: __('oro_frontend.input_widget.step_input.increase'),
            noWrap: true,
            icon: 'plus'
        }));

        const $input = this.$el.find('input');

        this.min = $input.attr('min') ?? 0;
        this.max = $input.attr('max') ?? Infinity;
        this.step = $input.attr('step');
        this.precision = $input.data('precision') === void 0 ? null : $input.data('precision');
    },

    /**
     * Increases input value
     */
    doIncrementValue() {
        const $input = this.$el.find('input');

        if (NumberFormatter.unformatStrict($input.val()) >= NumberFormatter.unformatStrict(this.max)) {
            return;
        }

        $input.val(
            NumberFormatter.unformatStrict($input.val()) + this._calculateStep()
        );
        this._afterSetInputValue();
    },

    /**
     * Decreases input value
     */
    doDecrementValue() {
        const $input = this.$el.find('input');

        if (NumberFormatter.unformatStrict($input.val()) <= NumberFormatter.unformatStrict(this.min)) {
            return;
        }

        $input.val(
            NumberFormatter.unformatStrict($input.val()) - this._calculateStep()
        );
        this._afterSetInputValue();
    },

    /**
     * Run validation or any other commands after input value is changed
     * @private
     */
    _afterSetInputValue(value) {
        const $input = this.$el.find('input');
        const validator = this.$el.find('input').closest('form').data('validator');

        if (validator) {
            validator.element($input);
        }
    },

    /**
     * Define incremental count
     * @returns {number}
     * @private
     */
    _calculateStep() {
        if (_.isUndefined(this.step) && !_.isUndefined(this.precision)) {
            return Math.pow(10, -this.precision);
        }

        if (_.isUndefined(this.step) && _.isUndefined(this.precision)) {
            return 1;
        }

        if (this.step === 'any') {
            return 1;
        }

        return this.step;
    },

    /**
     * @inheritdoc
     */
    render() {
        if ($.contains(this.el, this.subview('minus').el)) {
            this.subview('minus').$el.remove();
        }

        if ($.contains(this.el, this.subview('plus').el)) {
            this.subview('plus').$el.remove();
        }

        if (this.$el.children().length > 1) {
            throw new Error('Input widget container must be empty contain only one input field');
        }

        this.$el.addClass('form-quantity-row');

        this.$el.prepend(this.subview('minus').render().$el);
        this.$el.append(this.subview('plus').render().$el);

        const $input = this.$el.find('input');
        this.$el.find('input').attr('inputmode', 'decimal');

        if ($(`[for="${$input.attr('id')}"]`).length === 0) {
            $input.attr('aria-label', __('oro_frontend.input_widget.step_input.aria_label'));
        }

        return this;
    }
});

export default IncrementInputView;

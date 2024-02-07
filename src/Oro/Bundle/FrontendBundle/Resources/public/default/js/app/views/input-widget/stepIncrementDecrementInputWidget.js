import _ from 'underscore';
import NumberFormatter from 'orolocale/js/formatter/number';
import FrontendNumberInputWidget from './number';
import ButtonInputView from './buttonInputView';

const StepIncrementDecrementInputWidget = FrontendNumberInputWidget.extend({
    autoRender: true,

    min: null,

    max: null,

    step: null,

    incrementValue() {
        if (NumberFormatter.unformatStrict(this.$el.val()) >= NumberFormatter.unformatStrict(this.max)) {
            return;
        }

        const step = this._calculateStep();
        this._updateInputValue(NumberFormatter.unformatStrict(this.$el.val()) + step);
    },

    decrementValue() {
        if (NumberFormatter.unformatStrict(this.$el.val()) <= NumberFormatter.unformatStrict(this.min)) {
            return;
        }

        const step = this._calculateStep();
        this._updateInputValue(NumberFormatter.unformatStrict(this.$el.val()) - step);
    },

    _updateInputValue(value) {
        this.$el.val(value);
        this.$el.trigger('change');
        this.$el.trigger('number-widget:change');
    },

    _rememberAttr() {
        this.min = this.$el.attr('min') ?? 1;
        this.max = this.$el.attr('max') ?? Infinity;
        this.step = this.$el.attr('step') === 'any' ? 1 : this.$el.attr('step') ?? 1;
        FrontendNumberInputWidget.__super__._rememberAttr.call(this);
    },

    _calculateStep() {
        return this.step * Math.pow(10, -this.precision);
    },

    render() {
        FrontendNumberInputWidget.__super__.render.call(this);

        this.subview('minus', new ButtonInputView({
            container: this.$el.parent(),
            dataType: 'decrement',
            extraClass: 'input-quantity-btn--minus',
            noWrap: true,
            onClick: this.decrementValue.bind(this),
            icon: 'minus'
        }));

        this.subview('plus', new ButtonInputView({
            container: this.$el.parent(),
            dataType: 'increment',
            extraClass: 'input-quantity-btn--plus',
            noWrap: true,
            onClick: this.incrementValue.bind(this),
            icon: 'plus'
        }));

        return this;
    }
});

export default StepIncrementDecrementInputWidget;

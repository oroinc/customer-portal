import _ from 'underscore';
import FrontendNumberInputWidget from './number';
import ButtonInputView from './buttonInputView';

const StepIncrementDecrementInputWidget = FrontendNumberInputWidget.extend({
    autoRender: true,

    events: {
        'click button[data-type="increment"]': 'incrementValue',
        'click .input-quantity-btn--minus': 'decrementValue',
    },

    initialize(options) {
        FrontendNumberInputWidget.__super__.initialize.call(this, options);
        this.$el.parent().css('position', 'relative');
    },

    incrementValue() {
        console.log('incrementValue ->', );

        this.$el.val(Number(this.$el.val()) + 1);
    },

    decrementValue() {
        console.log('decrementValue ->', );

        if (Number(this.$el.val()) <= 1) {
            return;
        }

        this.$el.val(Number(this.$el.val()) - 1);
    },

    render() {
        FrontendNumberInputWidget.__super__.render.call(this);

        this.subview('minus', new ButtonInputView({
            container: this.$el.parent(),
            dataType: 'decrement',
            extraClass: 'input-quantity-btn--minus',
            noWrap: true,
            icon: 'minus'
        }));

        this.subview('plus', new ButtonInputView({
            container: this.$el.parent(),
            dataType: 'increment',
            extraClass: 'input-quantity-btn--plus',
            noWrap: true,
            icon: 'plus'
        }));

        return this;
    }
});

export default StepIncrementDecrementInputWidget;

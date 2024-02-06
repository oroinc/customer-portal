import FrontendNumberInputWidget from './number';

const QuantityInputWidget = FrontendNumberInputWidget.extend({
    allowZero: false,

    template: 'HELLO?',

    events: {
        'click [data-type="increase"]': 'increaseValue',
        'click [data-type="decrease"]': 'decreaseValue',
    },


});

export default QuantityInputWidget;

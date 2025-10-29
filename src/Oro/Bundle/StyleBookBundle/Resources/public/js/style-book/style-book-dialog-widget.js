import DialogWidget from 'oro/dialog-widget';
import $ from 'jquery';
import _ from 'underscore';
'use string';

const StyleBookDialogWidget = DialogWidget.extend({
    content: null,
    /**
     * @inheritdoc
     */
    constructor: function StyleBookDialogWidget(options) {
        StyleBookDialogWidget.__super__.constructor.call(this, options);
    },

    initialize: function(options) {
        options.url = '';

        this.content = options.content;

        StyleBookDialogWidget.__super__.initialize.call(this, options);
    },

    isActual: function() {
        return !this.disposed;
    },

    _onContentLoad: function(content) {
        content = $(content).wrapAll('<div />');

        const demoContent = $('<div />');
        demoContent.text(this.content);
        demoContent.prependTo(content);

        content.find('.widget-actions').append('<button class="btn btn--outlined">Accept</button>');
        content = content.parent().html();
        return StyleBookDialogWidget.__super__._onContentLoad.call(this, content);
    },

    closeHandler: function(onClose) {
        if (_.isFunction(onClose)) {
            onClose();
        }
    }
});

export default StyleBookDialogWidget;

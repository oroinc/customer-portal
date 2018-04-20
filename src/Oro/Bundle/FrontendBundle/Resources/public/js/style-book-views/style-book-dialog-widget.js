define(function(require) {
    'use string';

    var StyleBookDialogWidget;
    var DialogWidget = require('oro/dialog-widget');
    var $ = require('jquery');

    StyleBookDialogWidget = DialogWidget.extend({
        content: null,
        /**
         * @inheritDoc
         */
        constructor: function StyleBookDialogWidget() {
            StyleBookDialogWidget.__super__.constructor.apply(this, arguments);
        },

        initialize: function(options) {
            options.url = '/about';

            this.content = options.content;

            StyleBookDialogWidget.__super__.initialize.apply(this, arguments);
        },

        _onContentLoad: function(content) {
            content = $(content).wrapAll('<div />');

            var demoContent = $('<div />');
            demoContent.text(this.content);
            demoContent.prependTo(content);

            content.find('.widget-actions').append('<button class="btn btn--info">Accept</button>');
            content = content.parent().html();
            return StyleBookDialogWidget.__super__._onContentLoad.call(this, content);
        }
    });

    return StyleBookDialogWidget;
});

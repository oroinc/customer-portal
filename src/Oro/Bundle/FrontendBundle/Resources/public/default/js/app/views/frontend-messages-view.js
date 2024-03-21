import $ from 'jquery';
import {debounce} from 'underscore';
import PageMessagesView from 'oroui/js/app/views/page/messages-view';

const FrontendPageMessageView = PageMessagesView.extend({
    listen: {
        'layout:adjustHeight mediator': 'alignMessage'
    },

    constructor: function FrontendPageMessageView(options) {
        this.alignMessage = debounce(this.alignMessage.bind(this), 150);
        FrontendPageMessageView.__super__.constructor.call(this, options);
    },

    delegateEvents: function() {
        PageMessagesView.__super__.delegateEvents.call(this);
        $(window).on('scroll' + this.eventNamespace(), this.alignMessage.bind(this));
    },

    undelegateEvents: function() {
        PageMessagesView.__super__.undelegateEvents.call(this);
        $(window).off('scroll' + this.eventNamespace());
    },

    alignMessage() {
        this._setupNotificationOffset();
    },

    _setupNotificationOffset() {
        const header = document.querySelector('[data-page-header]');
        const {height, top} = header.getBoundingClientRect();
        this.$el.css('--notification-extra-offset-top', `${Math.max(0, height + top)}px`);
    }
});

export default FrontendPageMessageView;

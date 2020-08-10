define(function(require) {
    'use strict';

    const $ = require('jquery');
    require('jquery-ui/widget');

    $.widget('oroui.expandLongTexWidget', {
        options: {
            maxLength: 80,
            clipSymbols: ' ...',
            containerClass: 'expand-text',
            iconClass: 'fa-caret-right',
            openClass: 'open'
        },

        _create: function() {
            this._super();
            this.$el = this.element;
            this._prepareClasses();
        },

        _prepareClasses: function() {
            this.triggerClass = this.options.containerClass + '__trigger';
            this.contentClass = this.options.containerClass + '__container';
            this.textClass = this.options.containerClass + '__content';
            this.contentShoerClass = this.textClass + ' ' + this.textClass + '--short';
            this.contentLongClass = this.textClass + ' ' + this.textClass + '--long';
        },

        _init: function() {
            const text = this.$el.text().trim();
            if (text.length <= this.options.maxLength) {
                this.$el.text(text);
                return;
            }

            const shortText = text.substr(0, this.options.maxLength) + this.options.clipSymbols;
            const $trigger = this._createNode('span', this.triggerClass)
                .append(this._createNode('i', this.options.iconClass));
            const $shortContent = this._createNode('span', this.contentShoerClass, shortText);
            const $longContent = this._createNode('span', this.contentLongClass, text);

            const $content = this._createNode('div', this.contentClass);
            $content
                .append($trigger)
                .append($shortContent)
                .append($longContent);

            this.$el
                .html($content)
                .addClass('init');

            this._initEvents();
        },

        _initEvents: function() {
            const $trigger = this.$el.find('.' + this.triggerClass);

            this._on($trigger, {
                click: this._onClick
            });
        },

        _onClick: function(event) {
            event.preventDefault();
            this.$el.toggleClass(this.options.openClass);
        },

        _createNode: function(tag, className, content) {
            return $('<' + tag + '/>')
                .addClass(className || '')
                .html(content || '');
        }
    });

    return 'expandLongTexWidget';
});

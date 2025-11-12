import BaseComponent from 'oroui/js/app/components/base/component';
import mediator from 'oroui/js/mediator';
import _ from 'underscore';
import $ from 'jquery';

const EmptyItemsComponent = BaseComponent.extend({
    hideElements: '[data-name="empty-items__hide"]',
    /**
     * @property {Object}
     */
    options: {
        eventName: 'item:delete',
        hiddenClass: 'hidden'
    },
    /**
     * @inheritdoc
     */
    constructor: function EmptyItemsComponent(options) {
        EmptyItemsComponent.__super__.constructor.call(this, options);
    },
    /**
     * @param {Object} options
     */
    initialize: function(options) {
        this.options = _.extend(this.options, options);
        this.$el = options._sourceElement;

        mediator.on(this.options.eventName, this.showEmptyMessage, this);
    },
    showEmptyMessage: function() {
        const itemsSelector = this.$el.data('items-selector') || '.itemsSelectorContainer';
        const emptyBlockSelector = this.$el.data('empty-block-selector') || '.emptyBlockSelectorContainer';
        if (this.$el.find(itemsSelector).length === 0) {
            this.$el.remove();
            $(emptyBlockSelector).removeClass(this.options.hiddenClass);
            $(this.hideElements).remove();
        }
    },
    /**
     * @inheritdoc
     */
    dispose: function() {
        if (this.disposed) {
            return;
        }
        delete this.hideElements;

        mediator.off(this.options.eventName, this.showEmptyMessage, this);

        EmptyItemsComponent.__super__.dispose.call(this);
    }
});

export default EmptyItemsComponent;

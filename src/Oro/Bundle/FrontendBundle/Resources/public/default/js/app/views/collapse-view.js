import $ from 'jquery';
import {defaults} from 'underscore';
import BaseView from 'oroui/js/app/views/base/view';

const CollapseView = BaseView.extend({
    /**
     * @inheritdoc
     */
    optionNames: BaseView.prototype.optionNames.concat([
        'toggleAttrs', 'collapseAttrs', 'triggerIsFirst', 'triggerIconIsFirst'
    ]),

    /**
     * @inheritdoc
     */
    noWrap: true,

    /**
     * @inheritdoc
     */
    autoRender: true,

    /**
     * @inheritdoc
     */
    keepElement: true,

    /**
     * Render the trigger button before or after the collapse container
     */
    triggerIsFirst: true,

    /**
     * Render the trigger icon before or after the text
     */
    triggerIconIsFirst: true,

    /**
     * @inheritdoc
     */
    constructor: function CollapseView(options) {
        CollapseView.__super__.constructor.call(this, options);
    },

    /**
     * @inheritdoc
     */
    initialize(options) {
        this.collapseAttrs = defaults({}, options.collapseAttrs || {}, {
            'id': this.cid,
            'data-toggle': 'false'
        });
        this.toggleAttrs = defaults({}, options.toggleAttrs || {}, {
            'type': 'button',
            'class': 'btn btn--link btn--size-s btn--no-x-offset collapse-toggle',
            'data-toggle': 'collapse',
            'data-target': `#${this.collapseAttrs.id}`,
            'aria-controls': this.collapseAttrs.id,
            'aria-expanded': false
        });

        CollapseView.__super__.initialize.call(this, options);
    },

    /**
     * @inheritdoc
     */
    render() {
        const idSelector = `#${this.cid}`;

        if ($(idSelector).length) {
            return this;
        }

        let content = [this.renderTrigger(), this.renderCollapse()];

        if (this.triggerIsFirst === false) {
            content = content.reverse();
        }

        this.$el.after(...content);
        $(idSelector).append(this.$el).collapse(
            this.collapseAttrs['data-check-overflow'] ? 'overflow' : 'restoreState'
        );

        return this;
    },

    /**
     * @returns {Query.Element}
     */
    renderTrigger() {
        const $trigger = $('<button></button>');
        const text = this.toggleAttrs['data-text'];
        const icon = this.toggleAttrs['data-icon'];
        let content = [];

        if (icon) {
            content.push(
                $('<span></span>').attr({'class': icon, 'data-icon': '', 'aria-hidden': true})
            );
        }

        if (text) {
            content.push(
                $('<span></span>').text(text).attr('data-text', '')
            );
        }

        if (this.triggerIconIsFirst === false) {
            content = content.reverse();
        }

        if (content.length) {
            $trigger.append(...content);
        }

        $trigger.attr(this.toggleAttrs);

        return $trigger;
    },

    /**
     * @returns {Query.Element}
     */
    renderCollapse() {
        const $collapse = $('<div></div>');

        $collapse.attr(this.collapseAttrs);

        return $collapse;
    },

    /**
     * @inheritdoc
     */
    dispose() {
        if (this.disposed) {
            return;
        }

        const idSelector = `#${this.cid}`;

        // Move the collapse content to the original place
        $(this.$el).insertBefore($(idSelector));
        $(idSelector).collapse('dispose');
        // Remove collapse container and trigger
        $(idSelector).remove();
        $(`[data-target="${idSelector}"]`).remove();

        CollapseView.__super__.dispose.call(this);
    }
});

export default CollapseView;

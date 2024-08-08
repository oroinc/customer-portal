import BaseView from 'oroui/js/app/views/base/view';
import template from 'tpl-loader!orofrontend/templates/frontend-collapseble-hints-view.html';

const FrontendCollapsableHintsView = BaseView.extend({
    optionNames: BaseView.prototype.optionNames.concat(['filters', 'filterManager']),

    className: 'btn filter-criteria-hint-item filter-criteria-hint-item-toggle',

    tagName: 'div',

    attributes: {
        type: 'button'
    },

    events: {
        click: 'onClick'
    },

    listen: {
        'layout:reposition mediator': 'update',
        'toggle-sidebar mediator': 'update'
    },

    template,

    toggled: false,

    constructor: function FrontendCollapsableHintsView(...args) {
        this.toHide = [];
        FrontendCollapsableHintsView.__super__.constructor.apply(this, args);
    },

    initialize(options) {
        this.listenTo(this.filterManager, 'visibility-change', this.update);
        FrontendCollapsableHintsView.__super__.initialize.call(this, options);
    },

    getTemplateData() {
        return {
            ...FrontendCollapsableHintsView.__super__.getTemplateData.call(this),
            count: this.toHide.length
        };
    },

    render() {
        FrontendCollapsableHintsView.__super__.render.call(this);
        this.checkHintsVisibility();
        return this;
    },

    checkHintsVisibility() {
        const toHideLength = this.toHide.length;
        const hintChips = Object.values(this.filters).map(filter => filter.getHintChips()).flat();

        this.toHide = hintChips.reverse().map(chips => {
            chips.toggleVisibility(true);
            if (!chips.isFitInContainer(this.el)) {
                chips.toggleVisibility(false);
                return chips;
            }
        }).filter(Boolean);

        this.container.toggleClass('filter-items-hint--has-hidden-items', !!this.toHide.length);
        this.$el.toggleClass('hidden', !this.toggled && !this.toHide.length);

        if (this.toggled && !this.toHide.length) {
            this.onToggleHidden(false);
        }

        if (toHideLength !== this.toHide.length) {
            this.render();
        }
    },

    update() {
        this.checkHintsVisibility();
    },

    onClick() {
        this.onToggleHidden(!this.toggled);
    },

    onToggleHidden(toggled) {
        this.toggled = toggled;
        this.container.toggleClass('filter-items-hint--multiline', this.toggled);
    }
});

export default FrontendCollapsableHintsView;

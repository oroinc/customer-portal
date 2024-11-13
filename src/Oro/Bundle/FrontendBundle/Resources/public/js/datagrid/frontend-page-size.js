import PageSize from 'orodatagrid/js/datagrid/page-size';
import UnitsAsRadioGroupView from 'oroproduct/js/app/views/units-as-radio-group-view';
import viewportManager from 'oroui/js/viewport-manager';

const FrontendPageSize = PageSize.extend({
    /**
     * @inheritdoc
     */
    optionNames: PageSize.prototype.optionNames.concat(['groupModeBreakpoint']),

    /**
     * Render pagination as group of buttons
     * @property {string}
     */
    groupModeBreakpoint: 'mobile-big',

    _attributes() {
        const attrs = {};

        if (this.gridName) {
            attrs['data-dom-relocation-options'] = JSON.stringify({
                responsive: [{
                    viewport: this.groupModeBreakpoint,
                    moveTo: `[data-group="pagination-${this.gridName}"]`,
                    endpointClass: 'display'
                }]
            });
        }

        return attrs;
    },

    /**
     * @inheritdoc
     */
    listen: {
        'viewport:change mediator': 'render'
    },

    constructor: function(...args) {
        FrontendPageSize.__super__.constructor.apply(this, args);
    },

    preinitialize(options) {
        this.collection = options.collection;

        if (!this.collection) {
            throw new TypeError('"collection" is required');
        }

        this.gridName = this.collection.inputName;
    },

    render() {
        FrontendPageSize.__super__.render.call(this);
        this.renderAsGroup();
        this.toggleView();
        return this;
    },

    toggleView() {
        this.$el.removeClass('hide');

        if (this.hidden || this.$el.is(':empty')) {
            this.$el.addClass('hide');
        } else {
            this.$el.removeClass('hide');
        }
        return this;
    },

    renderAsGroup() {
        const toDisplayAsGroup = viewportManager.isApplicable(this.groupModeBreakpoint);
        const $select = this.$('select');

        if (!toDisplayAsGroup || !$select.length) {
            this.$el.removeClass('hide');
            return this;
        }

        this.subview('radioGroup', new UnitsAsRadioGroupView({
            autoRender: true,
            units: this.items,
            $select: $select,
            icon: 'eye',
            title: 'oro_frontend.datagrid.pageSize.groupTitle'
        }));

        $select.after(this.subview('radioGroup').$el);
        this.$el.addClass('hide');


        return this;
    },

    disposeRadioGroup() {
        if (this.subview('radioGroup')) {
            this.removeSubview('radioGroup');
            this.$el.removeClass('hide');
        }
    },

    /**
     * @inheritdoc
     */
    dispose: function() {
        if (this.disposed) {
            return;
        }

        this.disposeRadioGroup();

        FrontendPageSize.__super__.dispose.call(this);
    }
});

export default FrontendPageSize;

define(function(require) {
    const ActionsPanel = require('orodatagrid/js/datagrid/actions-panel-mass');

    const FrontendActionsPanelMass = ActionsPanel.extend({
        listen: {
            'backgrid:selected collection': 'toggle',
            'backgrid:selectAll collection': 'toggle',
            'backgrid:selectAllVisible collection': 'toggle',
            'backgrid:selectNone collection': 'toggle'
        },

        className: 'toolbar-mass-actions-panel',

        _attributes() {
            const attrs = {};

            if (this.gridName) {
                attrs['data-dom-relocation-options'] = JSON.stringify({
                    responsive: [{
                        viewport: 'mobile-big',
                        moveTo: `[data-group="massactions-${this.gridName}"]`,
                        endpointClass: 'optimized'
                    }]
                });
            }

            return attrs;
        },

        constructor: function FrontendActionsPanelMass(...args) {
            FrontendActionsPanelMass.__super__.constructor.apply(this, args);
        },

        preinitialize: function(options) {
            this.collection = options.collection;

            if (!this.collection) {
                throw new TypeError('"collection" is required');
            }

            this.gridName = this.collection.inputName;
        },

        toggle() {
            const data = {};
            this.collection.trigger('backgrid:getSelected', data);

            const isSelected = Boolean(data.inset === false || data.selected.length);

            if (isSelected) {
                this.$el.removeClass('hidden');
            } else {
                this.$el.addClass('hidden');
            }

            this.$el.trigger('visibility-change', isSelected);
        },

        render() {
            FrontendActionsPanelMass.__super__.render.call(this);

            this.toggle();
            return this;
        }
    });

    return FrontendActionsPanelMass;
});

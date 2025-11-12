import ActionCell from 'oro/datagrid/cell/action-cell';
import _ from 'underscore';
import moduleConfig from 'module-config';
const config = moduleConfig(module.id);

const FrontendActionCell = ActionCell.extend({
    constructor: function FrontendActionCell(options) {
        return FrontendActionCell.__super__.constructor.call(this, options);
    },

    initialize: function(options) {
        FrontendActionCell.__super__.initialize.call(this, options);

        if (_.isMobile() && config.actionsHideCount) {
            this.actionsHideCount = config.actionsHideCount;
        }
    },

    /**
     * Add extra classes to launcher
     *
     * @param {orodatagrid.datagrid.ActionLauncher} launcher
     * @param {Object=} params
     * @return {jQuery} Rendered element wrapped with jQuery
     */
    decorateLauncherItem(launcher) {
        FrontendActionCell.__super__.decorateLauncherItem.call(this, launcher);

        if (!launcher.$el) {
            return this;
        }

        const extraClass = config?.extraClass[launcher.launcherMode];

        if (extraClass) {
            launcher.$el.addClass(extraClass);
        }

        if (launcher.divider && this.isDropdownActions) {
            launcher.$el.parent().addClass(launcher.dropdownDividerClassName);
        }

        return this;
    },

    /**
     * @inheritdoc
     */
    delegateEvents(events) {
        FrontendActionCell.__super__.delegateEvents.call(this, events);

        this.listenTo(this.main, 'grid-row-swipe:start', this.hideDropdown);

        return this;
    }
});

export default FrontendActionCell;

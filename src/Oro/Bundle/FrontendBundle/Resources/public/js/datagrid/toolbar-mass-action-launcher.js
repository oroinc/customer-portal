define(function(require) {
    'use strict';

    const _ = require('underscore');
    const mediator = require('oroui/js/mediator');
    const ActionLauncher = require('orodatagrid/js/datagrid/action-launcher');

    /**
     * @class ToolbarMassActionLauncher
     * @extends ActionLauncher
     */
    const ToolbarMassActionLauncher = ActionLauncher.extend({
        template: require('!tpl-loader!orofrontend/templates/datarid/toolbar-mass-action-launcher.html'),

        /**
         * @type {Object}
         */
        componentOptions: null,

        /**
         * @type {BaseComponent}
         */
        component: null,

        /**
         * @type {Constructor.<BaseComponent>}
         */
        componentConstructor: null,

        /** @property {String} */
        wrapperClassName: undefined,

        /**
         * @inheritdoc
         */
        constructor: function ToolbarMassActionLauncher(options) {
            ToolbarMassActionLauncher.__super__.constructor.call(this, options);
        },

        /**
         * @inheritdoc
         */
        initialize: function(options) {
            this.componentOptions = _.omit(options, ['action', 'componentConstructor']);
            this.componentConstructor = options.componentConstructor;
            this.componentOptions.grid = options.action.datagrid;
            if (options.wrapperClassName) {
                this.wrapperClassName = options.wrapperClassName;
            }
            mediator.on('layout:reposition', this._updateDropdown, this);

            ToolbarMassActionLauncher.__super__.initialize.call(this, options);
        },

        /**
         * @inheritdoc
         */
        getTemplateData: function() {
            const data = ToolbarMassActionLauncher.__super__.getTemplateData.call(this);
            data.wrapperClassName = this.wrapperClassName;
            return data;
        },

        /**
         * @inheritdoc
         */
        render: function() {
            ToolbarMassActionLauncher.__super__.render.call(this);
            this.componentOptions._sourceElement = this.$el;
            const Component = this.componentConstructor;
            this.component = new Component(this.componentOptions);

            return this;
        },

        /**
         * @inheritdoc
         */
        dispose: function() {
            if (this.disposed) {
                return;
            }

            if (this.component) {
                this.component.dispose();
            }
            delete this.component;
            delete this.componentOptions;
            ToolbarMassActionLauncher.__super__.dispose.call(this);
        }
    });

    return ToolbarMassActionLauncher;
});

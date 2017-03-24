define(function(require) {
    'use strict';

    var FrontendGridViewsView;

    var $ = require('jquery');
    var _ = require('underscore');
    var __ = require('orotranslation/js/translator');
    var mediator = require('oroui/js/mediator');
    var GridViewsView = require('orodatagrid/js/datagrid/grid-views/view');
    var DeleteConfirmation = require('orofrontend/js/app/components/delete-confirmation');

    FrontendGridViewsView = GridViewsView.extend({
        /** @property */
        template: '#template-frontend-datagrid-grid-view',

        /** @property */
        titleTemplate: '#template-frontend-datagrid-grid-view-label',

        route: 'oro_api_frontend_datagrid_gridview_default',

        /** @property */
        events: {
            'change [data-change-grid-view]': 'onChange',
            'click [data-role="save_as"]': 'onSave',
            'click [data-role="save-new"]': 'onSaveAs',
            'click [data-role="share"]': 'onShare',
            'click [data-role="unshare"]': 'onUnshare',
            'click [data-role="delete"]': 'onDelete',
            'click [data-role="rename"]': 'onRename',
            'click [data-switch-edit-mode]': 'switchEditMode'
        },

        /** @property 'modal' | 'inline' */
        editMode: 'inline',

        /** @property */
        DeleteConfirmation: DeleteConfirmation,

        /** @property */
        defaults: {
            DeleteConfirmationOptions: {
                content: __('Are you sure you want to delete this item?'),
                okButtonClass: 'btn ok btn--info'
            },
            actionsOptions: [
                {
                    name: 'save',
                    icon: 'floppy-o',
                    priority: 20
                },
                {
                    name: 'save_as',
                    icon: 'floppy-o',
                    priority: 20
                },
                {
                    name: 'rename',
                    icon: 'pencil',
                    priority: 30
                },
                {
                    name: 'share',
                    icon: 'reply-all',
                    priority: 10
                },
                {
                    name: 'unshare',
                    icon: 'share',
                    priority: 10
                },
                {
                    name: 'discard_changes',
                    priority: 10
                },
                {
                    name: 'delete',
                    icon: 'trash',
                    priority: 40
                },
                {
                    name: 'use_as_default',
                    icon: 'file-o',
                    priority: 10,
                    enabled: false

                }
            ]
        },

        /**
         * @param options
         */
        initialize: function(options) {
            FrontendGridViewsView.__super__.initialize.call(this, options);
        },

        onSave: function(e) {
            var self = this;
            var model = this._getEditableViewModel(e);
            var $button = this.switchEditMode(e, {mode: 'show', role: 'save-created'});

            this.$el.find('input[name=name]').val(model.get('label'));
            this.$el.find('input[name=is_default]').attr('checked', model.get('is_default'));

            $button.on('click', function(e) {
                e.stopPropagation();

                self._onSaveAsModel(model, self);
            });
        },

        /**
         * @param e
         */
        onSaveAs: function(e) {
            var self = this;
            var $button = this.switchEditMode(e, {mode: 'show', role: 'save-new'});

            $button.on('click', function(e) {
                e.stopPropagation();

                self._onSaveAsModel(self.$el);
            });
        },

        /**
         * @param e
         */
        onRename: function(e) {
            var self = this;
            var model = this._getEditableViewModel(e);
            var $button = this.switchEditMode(e, {mode: 'show', role: 'rename'});

            this.$el.find('input[name=name]').val(model.get('label'));
            this.$el.find('input[name=is_default]').attr('checked', model.get('is_default'));

            $button.on('click', function(e) {
                e.stopPropagation();

                self._onRenameSaveModel(model, self.$el);
            });
        },

        /**
         * @param event
         * @param options
         * @returns {Path|*|jQuery|HTMLElement}
         */
        switchEditMode: function(event, options) {
            var $this = $(event.currentTarget);
            var options = options || $this.data('switch-edit-options') || {};
            var mode = $this.data('switch-edit-mode') || options.mode; // 'hide' | 'show'
            var $buttonMain = this.$('[data-switch-edit-button]');
            var $switchEditModeContainer = this.$('[data-edit-container]');
            var $button = $switchEditModeContainer.find('[data-role='+ options.role +']') || $([]);

            if (mode === 'show') {
                $buttonMain.hide();
                $switchEditModeContainer.show();
            } else if (mode === 'hide') {
                $buttonMain.show();
                $switchEditModeContainer.hide();
            }

            $button
                .show()
                .siblings()
                .hide();

            return $button;
        },

        /**
         * @doc inherit
         */
        onChange: function(e) {
            var $this = $(e.target);
            var value = $this.val();

            this.changeView(value);
            this._updateTitle();

            this.prevState = this._getCurrentState();
            this.viewDirty = !this._isCurrentStateSynchronized();
        },

        /**
         * @param modal
         * @param model
         * @param response
         * @param options
         */
        onError: function(modal, model, response, options) {
            var jsonResponse = JSON.parse(response.responseText);
            var errors = jsonResponse.errors.children.label.errors;

            mediator.execute('showFlashMessage', 'error', _.first(errors));
        },

        /**
         * @param e
         * @returns {*}
         * @private
         */
        _getEditableViewModel: function(e) {
            var viewModel = $(e.currentTarget).data('choice-value');

            return this.viewsCollection.get({
                name: viewModel
            });
        },

        /**
         * @param e
         * @returns {*}
         * @private
         */
        _getModelForDelete: function(e) {
            return this._getEditableViewModel(e);
        },

        /**
         * @returns {Array}
         * @private
         */
        _getViewActions: function() {
            var actions = [];

            this.viewsCollection.each(function(View) {
                var actionsForView = this._getActions(View);

                actionsForView = this.updateActions(actionsForView);
                actionsForView = this.filterByPriority(actionsForView);

                actions.push(actionsForView);
            }, this);

            return actions;
        },

        /**
         * @param actions
         * @returns {*}
         */
        updateActions: function(actions) {
            var options = this.defaults.actionsOptions;

            _.each(actions, function(item, iterate) {
                var currentOptions = _.find(options, {'name': item.name}) || {};
                var filteredOptions = _.omit(currentOptions, 'name'); // skip 'name'

                _.extend(item, filteredOptions || {});
            }, this);

            return actions;
        },

        /**
         * @param collection
         * @returns {*}
         */
        filterByPriority: function(collection) {
            return collection.sort(function(a, b) {
                if (a.priority < b.priority) {
                    return -1;
                }

                if (a.priority > b.priority) {
                    return 1
                }

                return 0;
            });
        },

        /**
         * @param actions
         * @returns {Array}
         */
        showActions: function(actions) {
            var showActions = [];

            _.each(actions, function(action) {
                var showAction = _.some(action, function(actionItem) {
                    return actionItem.enabled;
                });

                showActions.push(showAction)
            });

            return showActions;
        }
    });

    return FrontendGridViewsView;
});

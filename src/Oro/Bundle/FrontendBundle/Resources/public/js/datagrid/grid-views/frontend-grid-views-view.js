define(function(require) {
    'use strict';

    var FrontendGridViewsView;

    var $ = require('jquery');
    var _ = require('underscore');
    var __ = require('orotranslation/js/translator');
    var GridViewsView = require('orodatagrid/js/datagrid/grid-views/view');
    var DeleteConfirmation = require('orofrontend/js/app/components/delete-confirmation');
    var error = require('oroui/js/error');

    FrontendGridViewsView = GridViewsView.extend({
        /** @property */
        template: '.js-frontend-datagrid-grid-views-tpl',

        /** @property */
        titleTemplate: '.js-frontend-datagrid-grid-view-label-tpl',

        route: 'oro_api_frontend_datagrid_gridview_default',

        /** @property */
        events: {
            'change [data-change-grid-view]': 'onChange',
            'click [data-role="save"]': 'onSave',
            'click [data-role="use_as_default"]': 'onUseAsDefault',
            'click [data-role="save-new"]': 'onSaveNew',
            'click [data-role="share"]': 'onShare',
            'click [data-role="unshare"]': 'onUnshare',
            'click [data-role="delete"]': 'onDelete',
            'click [data-role="rename"]': 'onRename',
            'click [data-role="discard_changes"]': 'onDiscardChanges',
            'click [data-switch-edit-mode]': 'switchEditMode',
            'click .dropdown-menu': 'onClickView',
            'keydown #frontend-grid-view-name': 'onKeyDown'
        },

        /** @property */
        DeleteConfirmation: DeleteConfirmation,

        /** @property */
        defaults: {
            actionsOptions: [
                {
                    name: 'save',
                    icon: 'floppy-o',
                    priority: 40
                },
                {
                    name: 'save_as',
                    icon: 'floppy-o',
                    priority: 40,
                    enabled: false
                },
                {
                    name: 'rename',
                    icon: 'pencil',
                    priority: 50
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
                    icon: 'undo',
                    priority: 30
                },
                {
                    name: 'delete',
                    icon: 'trash',
                    priority: 60
                },
                {
                    name: 'use_as_default',
                    icon: 'th',
                    priority: 20

                }
            ],
            DeleteConfirmationOptions: {
                content: __('Are you sure you want to delete this item?'),
                okButtonClass: 'btn ok btn--info'
            },
            elements: {
                gridViewName: 'input[name=name]',
                gridViewDefault: 'input[name=is_default]',
                gridViewUpdate: '[data-grid-view-update]'
            },
            titleOptions: {
                icon: null,
                iconClass: null,
                text: ''
            }
        },

        /** @property */
        hideTitle: $([]),
        /**
         * @param options
         */
        initialize: function(options) {
            var $selector = $(this.template).filter('[data-datagrid-views-name =' + options.gridName + ']');

            if ($selector.length) {
                this.template =  $selector.get(0);
            }

            if (_.isObject(options.gridViewsOptions)) {
                this.titleOptions = _.extend(
                    this.defaults.titleOptions,
                    _.pick(options.gridViewsOptions, ['icon', 'iconClass', 'text'])
                );

                if (this.titleOptions.text.length) {
                    options.title = __(this.titleOptions.text);
                }

                if (options.gridViewsOptions.hideTitle) {
                    this.hideTitle = $(options.gridViewsOptions.hideTitle);
                }
            }

            this.togglePageTitles(true);

            FrontendGridViewsView.__super__.initialize.call(this, options);
        },

        render: function() {
            FrontendGridViewsView.__super__.render.apply(this, arguments);

            this.$gridViewName = this.$(this.defaults.elements.gridViewName);
            this.$gridViewDefault = this.$(this.defaults.elements.gridViewDefault);
            this.$gridViewUpdate = this.$(this.defaults.elements.gridViewUpdate);
            this.initLayout();
            return this;
        },

        /**
         * @returns {HTMLElement}
         */
        renderTitle: function() {
            return this.titleTemplate({
                title: this._getCurrentViewLabel(),
                iconClass: this.titleOptions.iconClass,
                icon: this.titleOptions.icon
            });
        },

        dispose: function() {
            if (this.disposed) {
                return;
            }

            delete this.$gridViewName;
            delete this.$gridViewDefault;
            delete this.$gridViewUpdate;

            this.togglePageTitles();

            FrontendGridViewsView.__super__.dispose.call(this);
        },

        /**
         * @doc inherit
         */
        _bindEventListeners: function() {
            var self = this;

            this.$el.on('hide.bs.dropdown', function() {
                self.switchEditMode({}, 'hide');
            });

            FrontendGridViewsView.__super__._bindEventListeners.apply(this, arguments);
        },

        /**
         * @param e
         */
        onSave: function(e) {
            var model = this._getEditableViewModel(e);

            this._onSaveModel(model);
        },

        /**
         * @param e
         */
        onKeyDown: function(e) {
            if (e.which === 13) {
                this.$gridViewUpdate.trigger('click');
            }
        },

        /**
         * @param e
         */
        onSaveNew: function(e) {
            var self = this;

            this.switchEditMode(e, 'show');

            this.$gridViewUpdate
                .text(this.$gridViewUpdate.data('text-add'))
                .on('click', function(e) {
                    e.stopPropagation();
                    var data = self.getInputData(self.$el);
                    var model = self._createBaseViewModel(data);

                    self._onSaveAsModel(model);
                });
        },

        /**
         * @param e
         */
        onRename: function(e) {
            var self = this;
            var model = this._getEditableViewModel($(e.currentTarget));

            this.switchEditMode(e, 'show');

            this.fillForm({
                name: model.get('label'),
                is_default: model.get('is_default')
            });

            this.$gridViewUpdate
                .text(this.$gridViewUpdate.data('text-save'))
                .on('click', function(e) {
                    e.stopPropagation();
                    var data = self.getInputData(self.$el);

                    self._onRenameSaveModel(model, data);
                });
        },

        /**
         * @param event
         * @param options
         * @returns {Path|*|jQuery|HTMLElement}
         */
        switchEditMode: function(event, mode) {
            var $this = $(event.currentTarget);
            var modeState = $this.data('switch-edit-mode') || mode; // 'hide' | 'show'
            var $buttonMain = this.$('[data-switch-edit-button]');
            var $switchEditModeContainer = this.$('[data-edit-container]');

            this.$gridViewUpdate.off().text(this.$gridViewUpdate.data('text'));

            if (modeState === 'show') {
                $buttonMain.hide();
                $switchEditModeContainer.show();
            } else if (modeState === 'hide') {
                $buttonMain.show();
                $switchEditModeContainer.hide();
            }

            this.fillForm();
        },

        /**
         * @param data
         */
        fillForm: function(data) {
            var obj = _.extend({
                name: '',
                is_default: false
            }, data);

            this.$gridViewName.val(obj.name);
            this.$gridViewDefault.attr('checked', obj.is_default);
        },

        /**
         * @param {Event} e
         */
        onClickView: function(e) {
            if (!this.$(e.target).is('[data-close]')) {
                e.stopPropagation();
            }
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
         * @param {Event} e
         */
        onDiscardChanges: function(e) {
            this.changeView($(e.currentTarget).data('choice-value'));
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

            error.showError(_.first(errors));

            this.$el.trigger('show.bs.dropdown');

            this.switchEditMode({}, 'show');
        },

        /**
         * @param {HTML} element
         * @returns {*}
         * @private
         */
        _getEditableViewModel: function(element) {
            var viewModel = $(element).data('choice-value');

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
         * @param {object} GridView
         * @returns {boolean}
         * @private
         */
        _getViewIsDirty: function(GridView) {
            var isCurrent = this.collection.state.gridView === GridView.id;

            return this.viewDirty && isCurrent;
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
                    return 1;
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

                showActions.push(showAction);
            });

            return showActions;
        },

        /**
         * @param state
         */
        togglePageTitles: function(state) {
            state = _.isUndefined(state) ? false : state;

            this.hideTitle.toggleClass('hidden', state);
        }
    });

    return FrontendGridViewsView;
});

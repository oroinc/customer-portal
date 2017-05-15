define(function(require) {
    'use strict';

    var FrontendGridViewsView;

    var $ = require('jquery');
    var _ = require('underscore');
    var __ = require('orotranslation/js/translator');
    var GridViewsView = require('orodatagrid/js/datagrid/grid-views/view');
    var DeleteConfirmation = require('oroui/js/delete-confirmation');

    FrontendGridViewsView = GridViewsView.extend({
        /** @property */
        template: '.js-frontend-datagrid-grid-views-tpl',

        /** @property */
        titleTemplate: '.js-frontend-datagrid-grid-view-label-tpl',

        /** @property */
        errorTemplate: '#template-datagrid-view-name-error-modal',

        /** @property */
        defaultPrefix: __('oro_frontend.datagrid_views.all'),

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
                content: __('Are you sure you want to delete this item?')
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

        /** @property */
        showErrorMessage: false,
        /**
         * @param options
         */
        initialize: function(options) {
            var $selector = $(this.template).filter('[data-datagrid-views-name =' + options.gridName + ']');

            if ($selector.length) {
                this.template =  $selector.get(0);
            }

            this.nameErrorTemplate = _.template($(this.errorTemplate).html());

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
            var model = this._getEditableViewModel(e.currentTarget);

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
                .off()
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
            var model = this._getEditableViewModel(e.currentTarget);

            this.switchEditMode(e, 'show', model.get('is_default'));

            this.fillForm({
                name: model.get('label'),
                is_default: model.get('is_default')
            });

            this.$gridViewUpdate
                .off()
                .text(this.$gridViewUpdate.data('text-save'))
                .on('click', function(e) {
                    var data = self.getInputData(self.$el);

                    e.stopPropagation();

                    model.set(data);
                    self._onRenameSaveModel(model);
                });
        },

        /**
         * @param {object} event
         * @param {string} mode
         * @param {bool} [hideCheckbox] - undefined
         * @returns {Path|*|jQuery|HTMLElement}
         */

        switchEditMode: function(event, mode, hideCheckbox) {
            var $this = $(event.currentTarget);
            var modeState = $this.data('switch-edit-mode') || mode; // 'hide' | 'show'

            hideCheckbox = hideCheckbox || false;

            this.$('[data-checkbox-container]').toggleClass('hidden', hideCheckbox);

            this.$gridViewUpdate.text(
                this.$gridViewUpdate.data('text')
            );

            this.toggleEditForm(modeState);
            this.fillForm();
        },

        /**
         * @param {string} mode
         */
        toggleEditForm: function(mode) {
            var $buttonMain = this.$('[data-switch-edit-button]');
            var $switchEditModeContainer = this.$('[data-edit-container]');

            if (mode === 'show') {
                $buttonMain.hide();
                $switchEditModeContainer.show();
            } else if (mode === 'hide') {
                $buttonMain.show();
                $switchEditModeContainer.hide();
            }
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
         * @param model
         * @param response
         * @param options
         */
        onError: function(model, response, options) {
            this.$el.trigger('show.bs.dropdown');

            if (response.status === 400) {
                var jsonResponse = JSON.parse(response.responseText);
                var errors = jsonResponse.errors.children.label.errors;

                if (errors) {
                    this.setNameError(_.first(errors));
                    this.fillForm({
                        name: model.previous('label')
                    });
                    this.toggleEditForm('show');
                }
            }
        },

        /**
         * {DocInherit}
         */
        onGridViewsModelInvalid: function(errors) {
            this.setNameError(_.first(errors));
            this.toggleEditForm('show');
        },

        /**
         *  Remove container with validation errors
         */
        clearValidation: function() {
            this.$('.validation-failed').remove();
        },

        /**
         * @param {String} error
         */
        setNameError: function(error) {
            this.clearValidation();

            if (error) {
                error = this.nameErrorTemplate({
                    error: error
                });

                this.$gridViewName.after(error);
            }
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
         * @param {HTMl} element
         * @returns {*}
         * @private
         */
        _getModelForDelete: function(element) {
            return this._getEditableViewModel(element);
        },

        /**
         * @returns {Array}
         * @private
         */
        _getViewActions: function() {
            var actions = [];
            var actionsOptions = this.defaults.actionsOptions;
            var onlySystemView = this.viewsCollection.length === 1;

            this.viewsCollection.each(function(GridView) {
                var actionsForView = this._getActions(GridView);
                var useAsDefaultAction = _.find(actionsOptions, {name: 'use_as_default'});

                if (_.isObject(useAsDefaultAction)) {
                    if (GridView.get('type') === 'system') {
                        useAsDefaultAction.enabled =
                            typeof GridView !== 'undefined' &&
                            !GridView.get('is_default') &&
                            !!this._getCurrentDefaultViewModel() &&
                            !onlySystemView;
                    } else {
                        useAsDefaultAction.enabled =
                            typeof GridView !== 'undefined' &&
                            !GridView.get('is_default');
                    }
                }

                actionsForView = this.updateActionsOptions(actionsForView, actionsOptions);
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
         * @param {*} actions
         * @param {*} actionsOptions
         * @returns {*}
         */
        updateActionsOptions: function(actions, actionsOptions) {
            actionsOptions = actionsOptions || {};

            _.each(actions, function(item, iterate) {
                var currentOptions = _.find(actionsOptions, {'name': item.name}) || {};
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

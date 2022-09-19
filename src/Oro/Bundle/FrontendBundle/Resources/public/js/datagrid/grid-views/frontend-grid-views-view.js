define(function(require) {
    'use strict';

    const $ = require('jquery');
    const _ = require('underscore');
    const __ = require('orotranslation/js/translator');
    const tools = require('oroui/js/tools');
    const errorHandler = require('oroui/js/error');
    const manageFocus = require('oroui/js/tools/manage-focus').default;
    const GridViewsView = require('orodatagrid/js/datagrid/grid-views/view');
    const DeleteConfirmation = require('oroui/js/delete-confirmation');
    const errorTemplate = require('tpl-loader!orodatagrid/templates/datagrid/view-name-error-modal.html');
    require('jquery-ui/tabbable');

    const ESCAPE_KEY_CODE = 27;
    const ENTER_KEY_CODE = 13;

    const FrontendGridViewsView = GridViewsView.extend({
        /**
         * @inheritdoc
         */
        attributes: {
            'data-layout': 'separate'
        },

        /** @property */
        templateSelector: '.js-frontend-datagrid-grid-views-tpl',

        /** @property */
        errorTemplate: errorTemplate,

        /** @property */
        defaultPrefix: __('oro_frontend.datagrid_views.all'),

        /** @property */
        toggleAriaLabel: 'oro_frontend.datagrid_views.toggleAriaLabel',

        /**
         * @inheritdoc
         */
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
            'click [data-cancel-edit]': 'onCancelEdit',
            'click .dropdown-menu': 'onClickView',
            'click [data-grid-view-update]': 'onUpdate',
            'hide.bs.dropdown': 'onHideDropdown',
            'show.bs.dropdown': 'onOpenDropdown'
        },

        /** @property */
        DeleteConfirmation: DeleteConfirmation,

        /**
         * @inheritdoc
         */
        adjustDocumentTitle: false,

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
        hideTitle: null,

        /**
         * @inheritdoc
         */
        constructor: function FrontendGridViewsView(options) {
            FrontendGridViewsView.__super__.constructor.call(this, options);
        },

        /**
         * @inheritdoc
         * @param options
         */
        initialize: function(options) {
            // get template by datagrid name or first template on page
            let $selector = $(this.templateSelector).filter('[data-datagrid-views-name =' + options.gridName + ']');
            if (!$selector.length) {
                $selector = $(this.templateSelector);
            }
            this.template = _.template($selector.html() ?? '');
            this.templateSelector = null;

            this.errorTemplate = this.getTemplateFunction('errorTemplate');

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

        /**
         * @inheritdoc
         * @returns {FrontendGridViewsView}
         */
        render: function() {
            FrontendGridViewsView.__super__.render.call(this);

            this.$gridViewName = this.$(this.defaults.elements.gridViewName);
            this.$gridViewDefault = this.$(this.defaults.elements.gridViewDefault);
            this.$gridViewUpdate = this.$(this.defaults.elements.gridViewUpdate);
            this.$gridViewPopupContainer = this.$('[data-grid-view-popup-container]');
            this.$gridViewSwitchEditButton = this.$('[data-switch-edit-button]');
            this.$editContainer = this.$('[data-edit-container]');
            this.$('[data-role="subtitle"]').attr('id', `subtitle-${this.uniqueId}`);
            this.$('[data-role="grid-views-list"]').attr('aria-labelledby', `subtitle-${this.uniqueId}`);
            this.initLayout();
            this.restoreDropdownState();
            this.updateButtonLabel();

            return this;
        },

        /**
         * @returns {HTMLElement}
         */
        renderTitle: function() {
            const label = this._getCurrentViewLabel();

            return this.titleTemplate({
                uniqueId: this.uniqueId,
                title: label,
                toggleAriaLabel: __(this.toggleAriaLabel, {choiceName: label}),
                iconClass: this.titleOptions.iconClass,
                icon: this.titleOptions.icon
            });
        },

        /**
         * @inheritdoc
         */
        dispose: function() {
            if (this.disposed) {
                return;
            }

            this.$gridViewPopupContainer.off(`keydown${this.eventNamespace()}`);
            this.$gridViewUpdate.off();
            if (this._editableViewModel) {
                delete this._editableViewModel;
            }
            delete this.$gridViewName;
            delete this.$gridViewDefault;
            delete this.$gridViewUpdate;
            delete this.$gridViewPopupContainer;
            delete this.$gridViewSwitchEditButton;
            delete this.$editContainer;
            this.togglePageTitles();

            this.updateDropdownState(null);

            FrontendGridViewsView.__super__.dispose.call(this);
        },

        /**
         * @param {Event} e
         */
        onSave: function(e) {
            const model = this._getEditableViewModel(e.currentTarget);
            this.updateDropdownState({
                elToFocus: tools.getElementCSSPath(
                    this.getInputRelatedToAction(e.currentTarget)
                )
            });

            this._onSaveModel(model);
        },

        /**
         * @param {Event} e
         */
        onKeyDown: function(e) {
            if (
                e.which === ENTER_KEY_CODE &&
                document.activeElement === this.$gridViewName[0]
            ) {
                e.stopPropagation();
                this.$gridViewUpdate.trigger('click');
            } else if (
                e.which === ESCAPE_KEY_CODE &&
                document.activeElement === this.$gridViewName[0] &&
                this.$editContainer.hasClass('show')
            ) {
                e.stopPropagation();
                this.switchEditMode(e, 'hide');
                this.restoreDropdownState();
            }
        },

        /**
         * @param {Event} e
         */
        onUpdate: function(e) {
            e.stopPropagation();

            const data = this.getInputData(this.$el);

            if (this._editableViewModel !== void 0) {
                this._editableViewModel.set(data, {silent: true});
                this._onRenameSaveModel(this._editableViewModel);
            } else {
                const model = this._createBaseViewModel(data);

                if (model.isValid()) {
                    this._onSaveAsModel(model);
                }
            }
        },

        /**
         * @param {Event} e
         */
        onSaveNew: function(e) {
            if (this._editableViewModel) {
                delete this._editableViewModel;
            }
            this.updateDropdownState({
                elToFocus: tools.getElementCSSPath(this.$gridViewSwitchEditButton[0])
            });
            this.switchEditMode(e, 'show');
            this.$gridViewName.trigger('focus');
        },

        /**
         * @param {Event} e
         */
        onRename: function(e) {
            this._editableViewModel = this._getEditableViewModel(e.currentTarget);
            this.updateDropdownState({
                elToFocus: tools.getElementCSSPath(e.currentTarget)
            });
            this.switchEditMode(e, 'show', this._editableViewModel.get('is_default'));
            this.fillForm({
                name: this._editableViewModel.get('label'),
                is_default: this._editableViewModel.get('is_default')
            });
            this.$gridViewName.trigger('focus');
        },

        updateButtonLabel: function() {
            const text = this._editableViewModel === void 0
                ? this.$gridViewUpdate.data('text-add')
                : this.$gridViewUpdate.data('text-save');

            this.$gridViewUpdate.text(text);
        },

        /**
         * @param {object} event
         * @param {string} mode
         * @param {bool} [hideCheckbox] - undefined
         */
        switchEditMode: function(event, mode, hideCheckbox) {
            const $this = $(event.currentTarget);
            const modeState = $this.data('switch-edit-mode') || mode; // 'hide' | 'show'

            hideCheckbox = hideCheckbox || false;

            this.$('[data-checkbox-container]').toggleClass('hidden', hideCheckbox);

            this.updateButtonLabel();

            this.fillForm();
            this.toggleEditForm(modeState);
        },

        /**
         * @param {Event} e
         */
        onCancelEdit: function(e) {
            e.stopPropagation();

            this.switchEditMode({}, 'hide');
            this.restoreDropdownState();
        },

        /**
         * @param {Event} e
         */
        onOpenDropdown: function(e) {
            this.$gridViewPopupContainer
                .off(`keydown${this.eventNamespace()}`)
                .on(`keydown${this.eventNamespace()}`, e => this.onKeyDown(e));
            this.updateDropdownState({
                dropdownToggle: tools.getElementCSSPath(e.relatedTarget),
                event: e.type
            });
        },

        /**
         * @param {Event} e
         */
        onHideDropdown: function(e) {
            this.$gridViewPopupContainer.off(`keydown${this.eventNamespace()}`);
            this.switchEditMode({}, 'hide');
            this.updateDropdownState({
                event: e.type
            });
        },

        /**
         * @param {Object} state
         */
        updateDropdownState: function(state) {
            if (state === null) {
                delete this._dropdownState;
                return;
            }

            for (const [key, value] of Object.entries(state)) {
                if (this._dropdownState === void 0) {
                    this._dropdownState = {};
                }

                this._dropdownState[key] = value;
            }
        },

        /**
         * @returns {}
         */
        getDropdownState: function() {
            return {...this._dropdownState};
        },

        restoreDropdownState: function() {
            const state = this.getDropdownState();

            if (_.isEmpty(state)) {
                return;
            }

            $(state.dropdownToggle).dropdown(state.event);

            const $focusEl = $(state.elToFocus);

            if ($focusEl.is(':tabbable')) {
                $focusEl.trigger('focus');
            } else {
                manageFocus.focusTabbable(this.$gridViewPopupContainer);
            }
        },

        /**
         * @param {string} mode
         */
        toggleEditForm: function(mode) {
            if (mode === 'show') {
                this.$gridViewSwitchEditButton.addClass('hide');
                this.$editContainer.addClass('show');
            } else if (mode === 'hide') {
                this.$gridViewSwitchEditButton.removeClass('hide');
                this.$editContainer.removeClass('show');
            }
        },

        /**
         * @param data
         */
        fillForm: function(data) {
            const obj = _.extend({
                name: '',
                is_default: false
            }, data);

            this.clearValidation();
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
         * @param {Event} e
         */
        onChange: function(e) {
            const $this = $(e.target);
            const value = $this.val();

            this.updateDropdownState({
                elToFocus: tools.getElementCSSPath(e.target)
            });
            this.changeView(value);
            this._updateTitle();

            this.prevState = this._getCurrentState();
            this.viewDirty = !this._isCurrentStateSynchronized();
        },

        /**
         * @param {Event} e
         */
        onDiscardChanges: function(e) {
            this.updateDropdownState({
                elToFocus: tools.getElementCSSPath(
                    this.getInputRelatedToAction(e.currentTarget)
                )
            });
            this.changeView($(e.currentTarget).data('choice-value'));
        },

        /**
         * @param {Event} e
         */
        onUseAsDefault: function(e) {
            this.updateDropdownState({
                elToFocus: tools.getElementCSSPath(
                    this.getInputRelatedToAction(e.currentTarget)
                )
            });
            FrontendGridViewsView.__super__.onUseAsDefault.call(this, e);
        },

        /**
         * @param {Event} e
         */
        onShare: function(e) {
            this.updateDropdownState({
                elToFocus: tools.getElementCSSPath(
                    this.getInputRelatedToAction(e.currentTarget)
                )
            });
            FrontendGridViewsView.__super__.onShare.call(this, e);
        },

        /**
         * @param {Event} e
         */
        onUnshare: function(e) {
            this.updateDropdownState({
                elToFocus: tools.getElementCSSPath(
                    this.getInputRelatedToAction(e.currentTarget)
                )
            });
            FrontendGridViewsView.__super__.onUnshare.call(this, e);
        },

        /**
         * @param {Event} e
         */
        onDelete: function(e) {
            const {event, dropdownToggle} = this.getDropdownState();
            const state = {
                event: event,
                dropdownToggle: dropdownToggle,
                elToFocus: tools.getElementCSSPath(e.currentTarget)
            };

            const confirm = FrontendGridViewsView.__super__.onDelete.call(this, e);
            let restoreDropdown = true;

            confirm.on('ok', () => {
                state.elToFocus = null;
                restoreDropdown = false;
            }).on('close', () => {
                setTimeout(() => {
                    if (this.disposed) {
                        return;
                    }
                    this.updateDropdownState(state);
                    restoreDropdown && this.restoreDropdownState();
                }, 0);
            });

            return confirm;
        },

        /**
         * @param model
         * @param response
         * @param options
         */
        onError: function(model, response, options) {
            this.restoreDropdownState();

            if (response.status === 400) {
                const responseJSON = response.responseJSON;
                const errors = responseJSON.errors ? responseJSON.errors.children.label.errors : null;
                const message = responseJSON.message;
                const err = errors ? errors[0] : message;

                if (err) {
                    this.fillForm({
                        name: model.previous('label')
                    });
                    this.setNameError(err);
                    this.toggleEditForm('show');
                    this.$gridViewName.trigger('focus');
                }
            } else {
                errorHandler.showErrorInUI(response);
            }
        },

        /**
         * @inheritdoc
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
            this.$gridViewName
                .removeClass('error')
                .attr({
                    'aria-describedby': null,
                    'aria-invalid': null
                });
        },

        /**
         * @param {String} error
         */
        setNameError: function(error) {
            this.clearValidation();

            if (error) {
                const id = _.uniqueId('error-');

                error = this.errorTemplate({
                    error: error,
                    id: id
                });

                this.$gridViewName
                    .addClass('error')
                    .attr({
                        'aria-describedby': id,
                        'aria-invalid': true
                    })
                    .after(error);
            }
        },

        /**
         * @param {HTMLElement} element
         * @returns {*}
         * @private
         */
        _getEditableViewModel: function(element) {
            const viewModel = $(element).data('choice-value');

            return this.viewsCollection.get({
                name: viewModel
            });
        },

        /**
         * @param {HTMLElement} element
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
            const actions = [];
            const actionsOptions = this.defaults.actionsOptions;
            const onlySystemView = this.viewsCollection.length === 1;

            this.viewsCollection.each(function(GridView) {
                let actionsForView = this._getActions(GridView);
                const useAsDefaultAction = _.find(actionsOptions, {name: 'use_as_default'});

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
            const isCurrent = this.collection.state.gridView === GridView.id;

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
                const currentOptions = _.find(actionsOptions, {name: item.name}) || {};
                const filteredOptions = _.omit(currentOptions, 'name'); // skip 'name'

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
            const showActions = [];

            _.each(actions, function(action) {
                const showAction = _.some(action, function(actionItem) {
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
            if (this.hideTitle) {
                this.hideTitle.toggleClass('hidden', _.isUndefined(state) ? false : state);
            }
        },

        /**
         * @param {HTMLElement} action
         * @returns HTMLElement
         */
        getInputRelatedToAction: function(action) {
            const relatedInput = this.$('[data-change-grid-view]')
                .filter((index, el) => el.value === action.getAttribute('data-choice-value'));

            return relatedInput.length ? relatedInput[0] : this.$('[data-change-grid-view]')[0];
        }
    });

    return FrontendGridViewsView;
});

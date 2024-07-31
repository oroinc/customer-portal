import $ from 'jquery';
import _ from 'underscore';
import __ from 'orotranslation/js/translator';
import mediator from 'oroui/js/mediator';
import Backbone from 'backbone';
import AbstractInputWidgetView from 'oroui/js/app/views/input-widget/abstract';
import dropdownTemplate from 'tpl-loader!orofrontend/default/templates/input-widget/responsive-dropdown.html';
import viewportManager from 'oroui/js/viewport-manager';

const ResponsiveDropdownWidgetView = AbstractInputWidgetView.extend({
    /**
     * @inheritdoc
     */
    firstListenTo: Backbone.Events.firstListenTo,

    /**
     * Selector for actions to move to dropdown
     * @property {string}
     */
    actionsSelector: 'a, button',

    /**
     * selector for a container that actions to move
     * @property {string}
     */
    ignoreActionsIn: '.dropdown-menu',

    /**
     * CSS breakpoint when to create a dropdown
     * @property {string}
     */
    screenThreshold: 'mobile-big',

    /**
     * Determined to create a dropdown based on the root element's width
     * @property {number|undefined}
     */
    widthThreshold: void 0,

    /** @property */
    dropdownTemplate: dropdownTemplate,

    /**
     * @property {string}
     */
    dropdownClass: '',

    /**
     * @property {string}
     */
    dropdownMenuClass: '',

    /**
     * @property {string}
     */
    toggleClass: 'btn--neutral',

    /**
     * @property {string}
     */
    text: '',

    /**
     * @property {string}
     */
    toggleAriaLabel: __('oro_frontend.responsive_dropdown.aria_label'),

    /**
     * @property {string}
     */
    placement: 'bottom-end',

    /**
     * @property {string}
     */
    icon: 'more-horizontal',

    /**
     * @property {string}
     */
    iconClass: '',

    /**
     * @property {string}
     */
    placeholderClass: 'simple-placeholder',

    /**
     * @property {string}
     */
    animationClass: 'simple-placeholder-animation',

    /**
     * @property {string}
     */
    actionsContainerClass: '',

    /**
     * @inheritdoc
     */
    constructor: function ResponsiveDropdownWidgetView(options) {
        this.UID = _.uniqueId('widget-');
        ResponsiveDropdownWidgetView.__super__.constructor.call(this, options);
    },

    /**
     * Find widget root element
     * @returns {jQuery.Element}
     */
    findContainer() {
        return this.$el;
    },

    /**
     * Nothing to do
     * @inheritdoc
     */
    widgetFunction() {
        return this;
    },

    /**
     * @inheritdoc
     */
    initialize(options) {
        ResponsiveDropdownWidgetView.__super__.initialize.call(this, options);
        this.prepareElementForDropdown();
        this.$el.removeClass(this.placeholderClass);
    },

    delegateListeners: function() {
        ResponsiveDropdownWidgetView.__super__.delegateListeners.call(this);

        // Make transformation to prepare placeholders for elements that might move to dropdown
        this.firstListenTo(mediator, 'viewport:change', this.transformToDropdown);
        this.firstListenTo(mediator, 'layout:reposition', this.transformToDropdown);

        // Restore transformation to normal order, expecting moved elements to return to their original places
        this.listenTo(mediator, 'viewport:change', this.restoreOriginal);
        this.listenTo(mediator, 'layout:reposition', this.restoreOriginal);

        this.firstListenTo(mediator, 'layout:content-relocated', this.onContentRelocated);

        $(document).on(`visibility-change${this.eventNamespace()}`, event => {
            if (this.$dropdown && this.$dropdown.is('.show') && this.$dropdown[0].contains(event.target)) {
                this.$dropdown.find('[data-toggle="dropdown"]').dropdown('update');
            }
        });
    },

    /**
     * Creates / Delete a dropdown
     */
    prepareElementForDropdown() {
        if (this.doTransformation()) {
            this.createDropdown();
        } else {
            this.destroyDropdown();
        }
    },

    /**
     * @returns {boolean}
     */
    doTransformation() {
        if (typeof this.widthThreshold === 'number') {
            return this.$el.width() >= this.widthThreshold;
        }
        return viewportManager.isApplicable(this.screenThreshold);
    },

    /**
     * Transform actions to a dropdown if possible
     */
    transformToDropdown() {
        if (this.doTransformation()) {
            this.createDropdown();
        }
    },

    /**
     * Display actions as there are
     */
    restoreOriginal() {
        if (this.doTransformation() === false) {
            this.destroyDropdown();
        }
    },

    /**
     * Handler after content is relocated
     * @param {jquery.Element} $el
     */
    onContentRelocated($el) {
        if (!this.$dropdown || this.$dropdown.is('.show') || !this.animationClass) {
            return;
        }

        if (this.$dropdown[0].contains($el[0])) {
            this.$dropdown.one('animationend', () => {
                this.$dropdown.removeClass(this.animationClass);
            });
            this.$dropdown.addClass(this.animationClass);
        }
    },

    createDropdown() {
        if (document.contains(this.el) === false) {
            // Dropdown is already created
            return;
        }

        const $dropdown = this.$dropdown = $(this.dropdownTemplate(this.getDropdownData()));

        $dropdown.insertBefore(this.$el);
        this.$el.detach();

        const actions = this.makeDropdownItems();
        const $actionsContainer = this.getActionsContainer();

        $actionsContainer.trigger('content:remove');
        $actionsContainer.html(actions);
        $actionsContainer.trigger('content:changed');
    },

    destroyDropdown() {
        const $dropdown = this.getDropdown();

        if (document.contains($dropdown[0]) === false) {
            // Dropdown is already destroyed
            return;
        }

        this.$el.insertBefore($dropdown);
        $dropdown.remove();
        this.$el.trigger('content:changed');
        delete this.$dropdown;
    },

    /**
     * @returns {jQuery.Element}
     */
    getDropdown() {
        return $(`[data-dropdown="${this.UID}"]`);
    },

    /**
     * @returns {jQuery.Element}
     */
    getActionsContainer() {
        return this.getDropdown().find('[data-role="actions"]');
    },

    /**
     * @returns {Object}
     */
    getDropdownData() {
        const options = ['dropdownClass', 'dropdownMenuClass', 'toggleClass', 'text', 'toggleAriaLabel',
            'placement', 'icon', 'iconClass', 'actionsContainerClass'];

        return {
            UID: this.UID,
            ..._.pick(this, options)
        };
    },

    /**
     * @returns {jQuery.Element}
     */
    makeDropdownItems() {
        const $groups = this.$el.find('[data-group]');
        const prepareActions = $actions => {
            return $actions.tooltip('dispose').clone(true)
                .removeAttr('data-bound-input-widget')
                .removeData('inputWidget');
        };

        if ($groups.length === 0) {
            return prepareActions(this.getActionsForElement(this.$el));
        }

        return $groups.map((i, el) => {
            const $group = $(el).clone().empty();
            const $actions = prepareActions(this.getActionsForElement($(el)));

            $group.append($actions);
            return $group;
        }).get();
    },

    /**
     * @property {jQuery.Element} $el
     * @returns {jQuery.Element}
     */
    getActionsForElement($el) {
        return $el.find(this.actionsSelector).not(`${this.ignoreActionsIn} ${this.actionsSelector}`);
    },

    /**
     * Destroy widget
     * @inheritdoc
     */
    dispose: function() {
        if (this.disposed) {
            return;
        }

        this.destroyDropdown();

        return ResponsiveDropdownWidgetView.__super__.dispose.call(this);
    }
});

export default ResponsiveDropdownWidgetView;

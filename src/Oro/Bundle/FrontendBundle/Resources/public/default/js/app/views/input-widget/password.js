import AbstractInputWidgetView from 'oroui/js/app/views/input-widget/abstract';
import $ from 'jquery';
import _ from 'underscore';
import __ from 'orotranslation/js/translator';

const PasswordInputWidgetView = AbstractInputWidgetView.extend({
    /**
     * @property {string}
     */
    SHOW_PASSWORD_ICON: 'eye-off',

    /**
     * @property {string}
     */
    HIDE_PASSWORD_ICON: 'eye',

    /**
     * @inheritdoc
     */
    constructor: function PasswordInputWidgetView(options) {
        this.UID = _.uniqueId('widget-');
        PasswordInputWidgetView.__super__.constructor.call(this, options);
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
        this.$el.wrap('<div class="password-wrapper fields-row"/>').after(this.createButton());

        PasswordInputWidgetView.__super__.initialize.call(this, options);
    },

    /**
     * @inheritDoc
     */
    delegateEvents(events) {
        PasswordInputWidgetView.__super__.delegateEvents.call(this, events);

        $(document).on(`click${this.eventNamespace()}`, `[${this.getUIDAttribute()}]`, this.toggleType.bind(this));
    },

    /**
     * @inheritDoc
     */
    undelegateEvents() {
        $(document).off(this.eventNamespace());

        return PasswordInputWidgetView.__super__.undelegateEvents.call(this);
    },

    /**
     * Toggles an input's type and actualizes a button state
     */
    toggleType() {
        const $button = this.getButton();
        let inoutType = 'password';
        let icon = this.HIDE_PASSWORD_ICON;
        let ariaLabel = 'oro_frontend.form.password.aria_label.to_show';

        if (this.$el.attr('type') === inoutType) {
            inoutType = 'text';
            icon = this.SHOW_PASSWORD_ICON;
            ariaLabel = 'oro_frontend.form.password.aria_label.to_hide';
        }

        this.$el.attr('type', inoutType);

        $button
            .html(this.createIcon(icon))
            .attr('aria-label', __(ariaLabel));
    },

    /**
     * @returns {jQuery.Element}
     */
    getButton() {
        return $(`[${this.getUIDAttribute()}]`);
    },

    /**
     * @returns {string}
     */
    getUIDAttribute() {
        return `data-button="${this.UID}"`;
    },

    /**
     * @returns {jQuery.Element}
     */
    createButton() {
        const $button = $(`<button type="button" class="btn btn--simple" ${this.getUIDAttribute()}></button>`);

        $button
            .attr('aria-label', __('oro_frontend.form.password.aria_label.to_show'))
            .append(this.createIcon(this.HIDE_PASSWORD_ICON));

        return $button;
    },

    /**
     * @param {string} iconName
     * @returns {string}
     */
    createIcon(iconName) {
        if (!iconName) {
            return '';
        }

        return _.macros('oroui::renderIcon')({
            extraClass: 'theme-icon--medium',
            name: iconName
        });
    },

    /**
     * Destroy widget
     * @inheritdoc
     */
    dispose: function() {
        if (this.disposed) {
            return;
        }

        const $button = this.getButton();
        $button.remove();

        this.$el
            .unwrap()
            .attr('password');

        return PasswordInputWidgetView.__super__.dispose.call(this);
    }
});

export default PasswordInputWidgetView;

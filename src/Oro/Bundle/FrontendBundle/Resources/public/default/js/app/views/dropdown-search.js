import __ from 'orotranslation/js/translator';
import {debounce, pick} from 'underscore';
import BaseView from 'oroui/js/app/views/base/view';
import searchTemplate from 'tpl-loader!orofrontend/default/templates/dropdown-search.html';
import HighlightTextView from 'oroui/js/app/views/highlight-text-view';

const ESC_KEY_CODE = 27;

const DropdownSearch = BaseView.extend({
    optionNames: BaseView.prototype.optionNames.concat([
        'searchContainerSelector', 'searchClassName', 'inputClassName', 'inputPlaceholder', 'inputAriaLabel',
        'buttonClassName', 'buttonAriaLabel', 'iconClassName', 'actionIconClassName', 'highlightOptions'
    ]),

    events: {
        'input [data-role="quick-search"]': 'onSearch',
        'keydown [data-role="quick-search"]': 'preventClose',
        'click [data-role="clear-search"]': 'onClick'
    },

    searchTemplate: searchTemplate,

    noWrap: true,

    autoRender: true,

    searchContainerSelector: '[data-role="search"]',

    searchClassName: 'dropdown-search-container',

    inputClassName: 'input input--full input--size-s',

    inputPlaceholder: __('oro_frontend.dropdown.quick_search.placeholder'),

    inputAriaLabel: __('oro_frontend.dropdown.quick_search.aria_label'),

    buttonClassName: 'btn btn--plain',

    buttonAriaLabel: __('oro_frontend.dropdown.quick_search.clear'),

    iconClassName: 'fa fa-search fa--no-offset',

    actionIconClassName: 'fa fa-remove fa--no-offset',

    /**
     * @inheritdoc
     */
    constructor: function DropdownSearch(options) {
        DropdownSearch.__super__.constructor.call(this, options);
    },

    /**
     * @inheritdoc
     */
    initialize(options) {
        DropdownSearch.__super__.initialize.call(this, options);

        this.onSearch = debounce(this.onSearch, 200);
        this.createHighlight();
    },

    getHighlightOptions() {
        return Object.assign({
            notFoundClass: 'hide',
            alwaysDisplaySelector: '.dropdown-search',
            highlightSelectors: [
                'a.dropdown-item',
                'button.dropdown-item'
            ],
            toggleSelectors: {
                'li[role="menuitem"]': '.items-group',
                '.items-group': '.item-container'
            }
        }, this.highlightOptions || {});
    },

    createHighlight() {
        this.subview('highlight', new HighlightTextView({
            el: this.el,
            ...this.getHighlightOptions()
        }));
    },

    /**
     * @inheritdoc
     */
    getTemplateData() {
        const data = DropdownSearch.__super__.getTemplateData.call(this);
        const extraData = pick(this, ['searchClassName', 'inputClassName', 'inputPlaceholder', 'inputAriaLabel',
            'buttonClassName', 'buttonAriaLabel', 'iconClassName', 'actionIconClassName']);

        return {
            ...data,
            ...extraData
        };
    },

    /**
     * Remove text for input
     * @param silent
     */
    clearField(silent = false) {
        this.$('[data-role="quick-search"]').val('');

        if (!silent) {
            this.$('[data-role="quick-search"]')
                .trigger('input')
                .trigger('change');
        }
    },

    /**
     * Click on button handler
     * @param e
     */
    onClick(e) {
        e.preventDefault();
        e.stopPropagation();
        this.clearField();
        this.$('[data-role="quick-search"]').focus();
    },

    /**
     * Prevent closing dropdown if button ESC was pressed
     * @param e
     */
    preventClose(e) {
        if (e.keyCode === ESC_KEY_CODE) {
            e.stopPropagation();

            this.clearField();
        }
    },

    render() {
        this.renderSearch();
        return this;
    },

    renderSearch() {
        this.$(this.searchContainerSelector).html(searchTemplate(this.getTemplateData()));

        return this;
    },

    /**
     * On input text handler
     * @param e
     */
    onSearch(e) {
        const minHeight = this.$el.find('.item-container').outerHeight();

        this.$el.find('.item-container').css({
            minHeight
        });

        this.$el.find('[data-role="clear-search"]').attr('disabled', e.target.value.length === 0);

        this.subview('highlight').update(e.target.value);

        if (e.target.value.length > 0 &&
            !this.subview('highlight').isElementHighlighted(this.$el)
        ) {
            this.$el.find('.items-group').addClass('hide');
            if (!this.$el.find('.no-matches').length) {
                this.$('[data-role="search-container"]').append(
                    `<span class="no-matches" role="alert">
                        ${__('oro_frontend.dropdown.quick_search.no_match')}
                    </span>`
                );
            }
        } else {
            this.$el.find('[role="alert"]').remove();
        }
    }
});

export default DropdownSearch;

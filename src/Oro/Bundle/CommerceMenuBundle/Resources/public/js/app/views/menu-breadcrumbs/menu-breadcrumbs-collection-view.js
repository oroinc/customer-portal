import BaseCollectionView from 'oroui/js/app/views/base/collection-view';
import ScrollShadowView from 'orofrontend/js/app/views/scroll-shadow-view';
import MenuBreadcrumbsItemView from './menu-breadcrumbs-item-view';
import MenuBreadcrumbsCollection from './menu-breadcrumbs-collection';

const MenuBreadcrumbsCollectionView = BaseCollectionView.extend({
    optionNames: BaseCollectionView.prototype.optionNames.concat(['initialBreadcrumbs', 'showLast']),

    autoRender: true,

    className: 'breadcrumbs scrollable-container',

    itemView: MenuBreadcrumbsItemView,

    menuSelector: '[role="menu"]',

    showLast: false,

    constructor: function MenuBreadcrumbsCollectionView(...args) {
        this.initialBreadcrumbs = [];
        MenuBreadcrumbsCollectionView.__super__.constructor.apply(this, args);
    },

    initialize(options) {
        this.collection = new MenuBreadcrumbsCollection(this.initialBreadcrumbs);

        MenuBreadcrumbsCollectionView.__super__.initialize.call(this, options);

        this.subview('scroll-shadow', new ScrollShadowView({
            el: this.el
        }));
    },

    delegateEvents(events) {
        MenuBreadcrumbsCollectionView.__super__.delegateEvents.call(this, events);

        this.$el.closest(this.menuSelector).on(
            `menu-traveling:toggle-menu-item${this.eventNamespace()}`,
            this.onMenuToggled.bind(this)
        );

        return this;
    },

    undelegateEvents() {
        if (this.$el) {
            this.$el.closest(this.menuSelector).off(this.eventNamespace());
        }

        MenuBreadcrumbsCollectionView.__super__.undelegateEvents.call(this);
    },

    onMenuToggled({detail}) {
        const {item, force} = detail;

        if (force) {
            this.addItem(this.getItemData(item));
        } else {
            this.removeItem(this.getItemData(item));
        }
    },

    getItemData(item) {
        return {
            id: item.getAttribute('data-main-menu-item'),
            label: item.querySelector('[data-name="menu-label"]').innerText
        };
    },

    addItem(item) {
        this.collection.add(item, {merge: true});
        this.updateScroll();
    },

    removeItem(item) {
        this.collection.remove(this.collection.get(item.id));
        this.updateScroll();
    },

    updateScroll() {
        const scrollShadow = this.subview('scroll-shadow');
        const {blockStartClass, blockEndClass} = scrollShadow.options;
        scrollShadow.el.classList.remove(blockStartClass, blockEndClass);
        scrollShadow.update();
        scrollShadow.el.scrollLeft = scrollShadow.el.scrollWidth - scrollShadow.el.offsetWidth;
    }
});

export default MenuBreadcrumbsCollectionView;

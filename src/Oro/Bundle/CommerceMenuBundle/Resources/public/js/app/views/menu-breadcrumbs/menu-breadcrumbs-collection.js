import BaseCollection from 'oroui/js/app/models/base/collection';
import MenuBreadcrumbsItemModel from './menu-breadcrumbs-item-model';

const MenuBreadcrumbsCollection = BaseCollection.extend({
    model: MenuBreadcrumbsItemModel,

    constructor: function MenuBreadcrumbsCollection(...args) {
        MenuBreadcrumbsCollection.__super__.constructor.apply(this, args);
    },

    add(...args) {
        MenuBreadcrumbsCollection.__super__.add.apply(this, args);
        this.updateLastItem();
    },

    remove(...args) {
        MenuBreadcrumbsCollection.__super__.remove.apply(this, args);
        this.updateLastItem();
    },

    updateLastItem() {
        this.each((model, index, collection) => {
            model.set({
                first: index === 0,
                level: -(collection.length - index) + 1,
                separator: index < collection.length - 2,
                last: index === collection.length - 1
            });
        });
    },

    getCount() {
        return this.length;
    }
});

export default MenuBreadcrumbsCollection;

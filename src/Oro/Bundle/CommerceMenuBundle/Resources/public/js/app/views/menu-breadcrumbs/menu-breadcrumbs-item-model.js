import BaseModel from 'oroui/js/app/models/base/model';

const MenuBreadcrumbsItemModel = BaseModel.extend({
    defaults: {
        label: '',
        level: 0,
        separator: true,
        first: false,
        last: false
    },

    constructor: function MenuBreadcrumbsItemModel(...args) {
        MenuBreadcrumbsItemModel.__super__.constructor.apply(this, args);
    }
});

export default MenuBreadcrumbsItemModel;

import BaseView from 'oroui/js/app/views/base/view';
import mediator from 'oroui/js/mediator';
import template from 'tpl-loader!orocommercemenu/templates/menu-breadcrumbs/menu-breadcrumbs-item-view.html';

const MenuBreadcrumbsItemView = BaseView.extend({
    className: 'breadcrumbs__item',

    template,

    events: {
        click: 'onClick'
    },

    listen: {
        'change model': 'render'
    },

    constructor: function MenuBreadcrumbsItemView(...args) {
        MenuBreadcrumbsItemView.__super__.constructor.apply(this, args);
    },

    onClick(event) {
        event.stopPropagation();
        event.preventDefault();

        mediator.trigger('menu-traveling:toggle:previous-level', this.model.get('level'));
    }
});

export default MenuBreadcrumbsItemView;

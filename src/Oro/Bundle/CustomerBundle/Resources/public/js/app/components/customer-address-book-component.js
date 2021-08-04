define(function(require) {
    'use strict';

    const BaseComponent = require('oroui/js/app/components/base/component');
    const routing = require('routing');
    const AddressBook = require('oroaddress/js/address-book');
    const widgetManager = require('oroui/js/widget-manager');

    const CustomerAddressBook = BaseComponent.extend({
        optionNames: BaseComponent.prototype.optionNames.concat(['isAddressHtmlFormatted']),

        /**
         * @inheritdoc
         */
        constructor: function CustomerAddressBook(options) {
            CustomerAddressBook.__super__.constructor.call(this, options);
        },

        isAddressHtmlFormatted: false,

        /**
         * @inheritdoc
         */
        initialize: function(options) {
            widgetManager.getWidgetInstance(options.wid, widget => {
                /** @type oroaddress.AddressBook */
                const addressBook = new AddressBook({
                    el: '#address-book',
                    addressListUrl: options.addressListUrl,
                    addressCreateUrl: options.addressCreateUrl,
                    addressUpdateUrl: function(address) {
                        return routing.generate(
                            options.addressUpdateRouteName,
                            {id: address.get('id'), entityId: options.entityId}
                        );
                    },
                    addressDeleteUrl: function(address) {
                        return routing.generate(
                            options.addressDeleteRouteName,
                            {addressId: address.get('id'), entityId: options.entityId}
                        );
                    },
                    addressMapOptions: {phone: 'phone'},
                    isAddressHtmlFormatted: this.isAddressHtmlFormatted
                });
                widget.getAction('add_address', 'adopted', function(action) {
                    action.on('click', addressBook.createAddress.bind(addressBook));
                });
                addressBook
                    .getCollection()
                    .reset(JSON.parse(options.currentAddresses));
            });
        }
    });

    return CustomerAddressBook;
});

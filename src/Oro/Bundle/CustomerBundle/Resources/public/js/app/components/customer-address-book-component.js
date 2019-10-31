define(function(require) {
    'use strict';

    const BaseComponent = require('oroui/js/app/components/base/component');
    const _ = require('underscore');
    const routing = require('routing');
    const AddressBook = require('oroaddress/js/address-book');
    const widgetManager = require('oroui/js/widget-manager');

    const CustomerAddressBook = BaseComponent.extend({
        /**
         * @inheritDoc
         */
        constructor: function CustomerAddressBook(options) {
            CustomerAddressBook.__super__.constructor.call(this, options);
        },

        /**
         * @inheritDoc
         */
        initialize: function(options) {
            widgetManager.getWidgetInstance(options.wid, function(widget) {
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
                    addressMapOptions: {phone: 'phone'}
                });
                widget.getAction('add_address', 'adopted', function(action) {
                    action.on('click', _.bind(addressBook.createAddress, addressBook));
                });
                addressBook
                    .getCollection()
                    .reset(JSON.parse(options.currentAddresses));
            });
        }
    });

    return CustomerAddressBook;
});

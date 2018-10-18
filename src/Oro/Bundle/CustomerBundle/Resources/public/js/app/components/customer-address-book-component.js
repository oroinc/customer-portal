define(function(require) {
    'use strict';

    var CustomerAddressBook;
    var BaseComponent = require('oroui/js/app/components/base/component');
    var _ = require('underscore');
    var routing = require('routing');
    var AddressBook = require('oroaddress/js/address-book');
    var widgetManager = require('oroui/js/widget-manager');

    CustomerAddressBook = BaseComponent.extend({
        /**
         * @inheritDoc
         */
        constructor: function CustomerAddressBook() {
            CustomerAddressBook.__super__.constructor.apply(this, arguments);
        },

        /**
         * @inheritDoc
         */
        initialize: function(options) {
            widgetManager.getWidgetInstance(options.wid, function(widget) {
                /** @type oroaddress.AddressBook */
                var addressBook = new AddressBook({
                    el: '#address-book',
                    addressListUrl: options.addressListUrl,
                    addressCreateUrl: options.addressCreateUrl,
                    addressUpdateUrl: function() {
                        var address = arguments[0];
                        return routing.generate(
                            options.addressUpdateRouteName,
                            {id: address.get('id'), entityId: options.entityId}
                        );
                    },
                    addressDeleteUrl: function() {
                        var address = arguments[0];
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

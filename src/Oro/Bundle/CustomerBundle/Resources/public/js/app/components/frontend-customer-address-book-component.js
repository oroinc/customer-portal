import BaseComponent from 'oroui/js/app/components/base/component';
import _ from 'underscore';
import routing from 'routing';
import AddressBook from 'orocustomer/js/address-book';
import deleteConfirmation from 'oroui/js/delete-confirmation';

const CustomerAddressBook = BaseComponent.extend({
    /**
     * @property {Object}
     */
    defaultOptions: {
        entityId: null,
        addressListUrl: null,
        addressCreateUrl: null,
        addressUpdateRouteName: null,
        addressDeleteRouteName: null,
        currentAddresses: [],
        useFormDialog: false,
        template: '',
        manageAddressesLink: '',
        showMap: true
    },

    /**
     * @inheritdoc
     */
    constructor: function CustomerAddressBook(options) {
        CustomerAddressBook.__super__.constructor.call(this, options);
    },

    /**
     * @inheritdoc
     */
    initialize: function(options) {
        options = _.defaults(options || {}, this.defaultOptions);

        /** @type oroaddress.AddressBook */
        const addressBook = new AddressBook({
            el: options._sourceElement.get(0),
            template: options.template,
            manageAddressesLink: options.manageAddressesLink,
            addressListUrl: options.addressListUrl,
            addressCreateUrl: options.addressCreateUrl,
            addressUpdateUrl: function(address) {
                return routing.generate(
                    options.addressUpdateRouteName,
                    {id: address.get('id'), entityId: address.get('ownerId')}
                );
            },
            addressDeleteUrl: function(address) {
                return routing.generate(
                    options.addressDeleteRouteName,
                    {addressId: address.get('id'), entityId: address.get('ownerId')}
                );
            },
            addressesContainerHtml: '<ul class="map-address-list"></ul>',
            addressTagName: 'li',
            addressMapOptions: {phone: 'phone'},
            useFormDialog: options.useFormDialog,
            mapViewport: options.mapViewport,
            allowToRemovePrimary: true,
            confirmRemove: true,
            confirmRemoveComponent: deleteConfirmation,
            showMap: options.showMap
        });

        addressBook.getCollection().reset(JSON.parse(options.currentAddresses));
        options._sourceElement.children('.view-loading').remove();
    }
});

export default CustomerAddressBook;

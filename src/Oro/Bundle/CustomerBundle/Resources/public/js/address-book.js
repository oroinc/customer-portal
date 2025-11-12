import BaseAddressBook from 'oroaddress/js/address-book';
import $ from 'jquery';
import _ from 'underscore';
import mediator from 'oroui/js/mediator';
import viewportManager from 'oroui/js/viewport-manager';

const AddressBook = BaseAddressBook.extend({
    optionNames: BaseAddressBook.prototype.optionNames.concat(['useFormDialog', 'mapViewport']),

    useFormDialog: true,

    checkViewport: false,

    mapViewport: 'all',

    listen() {
        return {
            [`viewport:${this.mapViewport} mediator`]: '_checkMapVisibility'
        };
    },

    /**
     * @inheritdoc
     */
    constructor: function AddressBook(options) {
        AddressBook.__super__.constructor.call(this, options);
    },

    /**
     * @param {Object} options
     */
    initialize: function(options) {
        this.checkViewport = _.isUndefined(options.showMap) ? this.options.showMap : options.showMap;
        if (this.checkViewport && viewportManager.isApplicable(this.mapViewport)) {
            options.showMap = false;
        }
        AddressBook.__super__.initialize.call(this, options);
    },

    /**
     * @param {String} title
     * @param {String} url
     * @private
     */
    _openAddressEditForm: function(title, url) {
        if (this.useFormDialog) {
            AddressBook.__super__._openAddressEditForm.call(this, title, url);
        } else {
            mediator.execute('redirectTo', {url: url}, {redirect: true});
        }
    },

    addAll: function(items) {
        AddressBook.__super__.addAll.call(this, items);

        let $linkContainer = $(this.options.manageAddressesLink);

        $linkContainer.removeClass('hidden');
        if (
            $linkContainer.prop('nodeName') !== 'LI' &&
            ['OL', 'UL'].includes(this.$addressesContainer.prop('nodeName'))
        ) {
            $linkContainer.wrap('<li></li>');
            $linkContainer = $linkContainer.parent();
        }

        $linkContainer.appendTo(this.$addressesContainer);
    },

    _checkMapVisibility: function(e) {
        if (!this.checkViewport) {
            return;
        }
        this.options.showMap = !e.matches;
        if (this.options.showMap) {
            this.initializeMap();
        } else {
            this.disposeMap();
        }
    }
});

export default AddressBook;

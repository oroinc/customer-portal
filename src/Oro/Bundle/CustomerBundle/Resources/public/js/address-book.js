/*jslint nomen:true*/
/*global define*/
define(function(require) {
    'use strict';

    var AddressBook;
    var BaseAddressBook = require('oroaddress/js/address-book');
    var $ = require('jquery');
    var _ = require('underscore');
    var mediator = require('oroui/js/mediator');
    var viewportManager = require('oroui/js/viewport-manager');

    AddressBook = BaseAddressBook.extend({
        optionNames: BaseAddressBook.prototype.optionNames.concat(['useFormDialog', 'mapViewport']),

        useFormDialog: true,

        checkViewport: false,

        mapViewport: {},

        listen: {
            'viewport:change mediator': '_checkMapVisibility'
        },

        /**
         * @param {Object} options
         */
        initialize: function(options) {
            this.checkViewport = _.isUndefined(options.showMap) ? this.options.showMap : options.showMap;
            if (this.checkViewport && !viewportManager.isApplicable(this.mapViewport)) {
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
                AddressBook.__super__._openAddressEditForm.apply(this, arguments);
            } else {
                mediator.execute('redirectTo', {url: url}, {redirect: true});
            }
        },

        addAll: function() {
            AddressBook.__super__.addAll.apply(this, arguments);

            $(this.options.manageAddressesLink)
                .appendTo(this.$addressesContainer)
                .removeClass('hidden');
        },

        _checkMapVisibility: function(viewport) {
            if (!this.checkViewport) {
                return;
            }
            this.options.showMap = viewport.isApplicable(this.mapViewport);
            if (this.options.showMap) {
                this.initializeMap();
            } else {
                this.disposeMap();
            }
        }
    });

    return AddressBook;
});

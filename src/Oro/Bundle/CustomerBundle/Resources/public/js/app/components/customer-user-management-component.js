define(function(require) {
    'use strict';

    var CustomerUserManagementComponent;
    var BaseComponent = require('oroui/js/app/components/base/component');
    var mediator = require('oroui/js/mediator');
    var $ = require('jquery');

    CustomerUserManagementComponent = BaseComponent.extend({
        /**
         * @inheritDoc
         */
        constructor: function CustomerUserManagementComponent() {
            CustomerUserManagementComponent.__super__.constructor.apply(this, arguments);
        },

        /**
         * @inheritDoc
         */
        initialize: function(options) {
            options._sourceElement.find('a').on('click', function(e) {
                e.preventDefault();
                var el = $(this);
                $.ajax({
                    url: el.attr('href'),
                    type: 'GET',
                    success: function(response) {
                        if (response && response.message) {
                            mediator.once('page:afterChange', function() {
                                mediator.execute(
                                    'showFlashMessage',
                                    (response.successful ? 'success' : 'error'),
                                    response.message
                                );
                            });
                        }
                        mediator.execute('refreshPage');
                    }
                });
            });
        }
    });

    return CustomerUserManagementComponent;
});

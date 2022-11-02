define(function(require) {
    'use strict';

    const BaseComponent = require('oroui/js/app/components/base/component');
    const mediator = require('oroui/js/mediator');
    const $ = require('jquery');

    const CustomerUserManagementComponent = BaseComponent.extend({
        /**
         * @inheritdoc
         */
        constructor: function CustomerUserManagementComponent(options) {
            CustomerUserManagementComponent.__super__.constructor.call(this, options);
        },

        /**
         * @inheritdoc
         */
        initialize: function(options) {
            options._sourceElement.find('a').on('click', function(e) {
                e.preventDefault();
                const el = $(this);
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

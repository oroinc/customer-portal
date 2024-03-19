import $ from 'jquery';
import mediator from 'oroui/js/mediator';
import BaseComponent from 'oroui/js/app/components/base/component';
import NavigationMenuView from 'oronavigation/js/app/views/navigation-menu-view';

const NavigationMenuComponent = BaseComponent.extend({
    /**
     * @inheritdoc
     */
    constructor: function NavigationMenuComponent(options) {
        NavigationMenuComponent.__super__.constructor.call(this, options);
    },

    /**
     * @inheritdoc
     */
    initialize(options) {
        this.viewOptions = {
            el: options._sourceElement,
            ...options.viewOptions ?? {}
        };

        // Initialize a view if it is a visible
        if (
            options._sourceElement.is(':visible')
        ) {
            this.initView();
        }

        this.listenTo(mediator, 'viewport:change', () => {
            // Re-initialize a view after resizing if it is a visible
            if (options._sourceElement.is(':visible')) {
                this.initView();
            }
        });

        if (Array.isArray(options.listenToDOMEvents)) {
            options.listenToDOMEvents.forEach(DOMEvent => {
                const eventName = DOMEvent + this.eventNamespace();

                $(document).on(eventName, e => {
                    // View inside on an element that triggers event
                    if (options._sourceElement.parent(e.target)) {
                        this.initView();
                    }
                });
            });
        }
    },

    /**
     * Returns event's name space
     *
     * @returns {string}
     * @protected
     */
    eventNamespace: function() {
        return `.${this.cid}`;
    },

    /**
     * @param {Object} [options]
     *
     * Initializes navigation menu view
     */
    initView(options = {}) {
        this.disposeView();

        this.view = new NavigationMenuView({
            ...this.viewOptions,
            ...options
        });
        this.view.initControls();

        return this.view;
    },

    /**
     * Disposes navigation menu view
     */
    disposeView() {
        if (this.view && !this.view.disposed) {
            this.view.dispose();
            delete this.view;
        }
    },

    /**
     * @inheritdoc
     */
    dispose: function() {
        if (this.disposed) {
            return;
        }

        this.disposeView();
        delete this.viewOptions;
        $(document).on(this.eventNamespace());
        NavigationMenuComponent.__super__.dispose.call(this);
    }
});

export default NavigationMenuComponent;

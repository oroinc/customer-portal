define(function(require) {
    'use strict';

    var StyleBookPlayground;
    var BaseView = require('oroui/js/app/views/base/view');
    var styleBookPlaygroundTemplate = require('tpl!orofrontend/templates/style-book/style-book-playground.html');
    var tools = require('oroui/js/tools');
    var _ = require('underscore');
    var $ = require('jquery');

    /**
     * @exports StyleBookPlayground
     */
    StyleBookPlayground = BaseView.extend({
        /**
         * @property
         */
        optionNames: BaseView.prototype.optionNames.concat(
            ['props', 'viewConstructor', 'viewOptions', 'renderAfter']
        ),

        /**
         * @property
         */
        viewConstructor: null,

        /**
         * @property
         */
        viewOptions: {},

        /**
         * @property
         */
        props: {},

        /**
         * @property
         */
        events: {
            'change [data-name]': '_onChangeProps'
        },

        /**
         * @property
         */
        template: styleBookPlaygroundTemplate,

        /**
         * @property
         */
        configPreviewSelector: '[data-config]',

        /**
         * @property
         */
        viewPreviewSelector: '[data-playground-view]',

        /**
         * @property
         */
        renderAfter: 'demand',
        /**
         * @Constructor
         * @returns {*}
         */
        constructor: function StyleBookPlayground() {
            return StyleBookPlayground.__super__.constructor.apply(this, arguments);
        },

        /**
         * @Initialize
         * @param options
         */
        initialize: function(options) {
            this.viewOptions = _.extend({}, this.viewOptions);
            StyleBookPlayground.__super__.initialize.apply(this, arguments);

            tools.loadModules([this.viewConstructor], this.createView, this);
            this.createPlayground();
            this._onChangeProps = _.debounce(this._onChangeProps, 500);
        },

        /**
         * @createView
         * @param View
         */
        createView: function(View) {
            this.viewConstructor = View;
            this.constructorName = View.name;
            this.subview(this.constructorName, new View(this.viewOptions));

            if (this.renderAfter === 'ondemand') {
                this.subview(this.constructorName).render();
                this.subview(this.constructorName).$el.appendTo(this.$el.find(this.viewPreviewSelector));
            }

            if (this.renderAfter === 'onaction') {
                var actionEl = this.$('[data-action]');
                var actions = actionEl.data('action').split(' ');
                actionEl.on(actions[0], _.bind(this.renderViewViaMethod, this, actions[1]));
            }
        },

        /**
         * @disposeView
         */
        disposeView: function() {
            this.subview(this.constructorName).dispose();
        },

        /**
         * @updateConfigPreview
         */
        updateConfigPreview: function() {
            this.configPreview.text(JSON.stringify(this.viewOptions, null, '\t'));
        },

        /**
         * @renderViewViaMethod
         * @private
         */
        renderViewViaMethod: function(method) {
            this.subview(this.constructorName)[method]();
        },

        /**
         * @createPlayground
         */
        createPlayground: function() {
            this.$el.append(this.template({
                props: this._resolvePropsFormat(this.props)
            }));

            this.configPreview = this.$(this.configPreviewSelector);
            this.updateConfigPreview();
        },

        /**
         * @_resolvePropsFormat
         * @param props
         * @private
         */
        _resolvePropsFormat: function(props) {
            return _.mapObject(props, function(prop, key) {
                if (!_.isObject(prop)) {
                    prop = {
                        value: prop
                    };
                }

                if (!prop.type) {
                    prop['type'] = 'input';
                }
                if (!prop.label) {
                    prop['label'] = key;
                }
                if (!prop.bind) {
                    prop['bind'] = key;
                }
                console.log(prop)
                return prop;
            });
        },

        /**
         * @_getViewOptions
         * @private
         */
        _getViewOptions: function(options) {
            options = _.mapObject(_.clone(options), function(prop, key) {
                if (_.isObject(prop)) {
                    prop = prop.value;
                }
                return prop;
            });

            return options;
        },

        /**
         * @_onChangeProps
         * @param event
         * @private
         */
        _onChangeProps: function(event) {
            var $target = $(event.target);
            var name = $target.data('name');
            var value = $target.is(':checkbox') ? $target.is(':checked') : $target.val();
            this._setBindOption(name.split('.'), value, this.viewOptions);

            this.disposeView();
            this.createView(this.viewConstructor);
            this.updateConfigPreview();
        },

        _setBindOption: function(names, value, options) {
            var index = _.first(names);
            var _rest = _.rest(names);
            var option = options[index];

            if (_rest.length > 1) {
                return this._setBindOption(_rest, value, option);
            }

            option[_.first(_rest)] = _.isNaN(parseFloat(value)) ? value : parseFloat(value);
        }
    });

    return StyleBookPlayground;
});

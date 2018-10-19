define(function(require) {
    'use strict';

    var StyleBookPlayground;
    var BaseView = require('oroui/js/app/views/base/view');
    var styleBookPlaygroundTemplate = require('tpl!orostylebook/templates/style-book/style-book-playground.html');
    var tools = require('oroui/js/tools');
    var _ = require('underscore');
    var $ = require('jquery');

    /**
     * @exports StyleBookPlayground
     */
    StyleBookPlayground = BaseView.extend({
        /**
         * @inheritDoc
         * @property {Array}
         */
        optionNames: BaseView.prototype.optionNames.concat(
            ['props', 'viewConstructor', 'viewOptions', 'renderAfter', 'widget']
        ),

        /**
         * @property {Constructor}
         */
        viewConstructor: null,

        /**
         * @property {Object}
         */
        viewOptions: {},

        /**
         * @property {Object}
         */
        props: {},

        /**
         * @inheritDoc
         * @property {Object}
         */
        events: {
            'change [data-name]': '_onChangeProps'
        },

        /**
         * @property {Function}
         */
        template: styleBookPlaygroundTemplate,

        /**
         * @property {String}
         */
        configPreviewSelector: '[data-config]',

        /**
         * @property {String}
         */
        viewPreviewSelector: '[data-playground-view]',

        /**
         * @property {String}
         */
        playgroundPropsSelector: '[data-props]',

        /**
         * @property {String}
         */
        renderAfter: 'demand',

        /**
         * @property
         */
        subviewContainer: '[data-example-view]',

        /**
         * @property
         */
        widget: false,

        /**
         * @Constructor
         * @inheritDoc
         * @returns {*}
         */
        constructor: function StyleBookPlayground() {
            return StyleBookPlayground.__super__.constructor.apply(this, arguments);
        },

        /**
         * @Initialize
         * @inheritDoc
         * @param {Object} options
         */
        initialize: function(options) {
            this.viewOptions = _.extend({}, this.viewOptions);
            this.prepearProps(options.props);
            StyleBookPlayground.__super__.initialize.apply(this, arguments);

            tools.loadModules([this.viewConstructor], this.createView, this);
            this.createPlayground();
            this._onChangeProps = _.debounce(this._onChangeProps, 500);
        },

        /**
         * @createView
         * @param {Constructor} View
         */
        createView: function(View) {
            this.viewConstructor = View;

            if (_.isString(this.viewConstructor)) {
                this.$el[this.viewConstructor](this.viewOptions);
            }

            if (_.isFunction(this.viewConstructor)) {
                this.constructorName = View.name;

                if (this.$el.find(this.subviewContainer).length) {
                    _.extend(this.viewOptions, {
                        _sourceElement: this.$el.find(this.subviewContainer),
                        el: this.$el.find(this.subviewContainer).get()
                    });
                }

                this.subview(this.constructorName, new View(this.viewOptions));

                if (this.renderAfter === 'demand') {
                    if (!/Component$/.test('ContentSliderComponent')) {
                        this.subview(this.constructorName).render();
                    }
                    this.subview(this.constructorName).$el.appendTo(this.$el.find(this.viewPreviewSelector));
                }

                if (this.renderAfter === 'action') {
                    var actionEl = this.$('[data-action]');
                    var actions = actionEl.data('action').split(' ');
                    actionEl.on(actions[0], _.bind(this.renderViewViaMethod, this, actions[1]));
                }
            }

            if (this.widget) {
                this.$el.inputWidget('seekAndCreate');
            }
        },

        /**
         * Resolve and prepear props for playground
         * @param {Object} props
         */
        prepearProps: function(props) {
            _.each(props, function(prop, key) {
                if (_.isObject(prop)) {
                    prop = _.has(prop, 'value') ? prop.value : _.omit('label', 'type');
                }
                this._setBindOption(key.split('.'), prop, this.viewOptions);
            }, this);
        },

        /**
         * @disposeView
         */
        disposeView: function() {
            if (_.isString(this.viewConstructor)) {
                this.$el[this.viewConstructor]('destroy');
            }

            if (_.isFunction(this.viewConstructor)) {
                this.subview(this.constructorName).dispose();
            }
        },

        /**
         * @updateConfigPreview Update text preview of configuration array
         */
        updateConfigPreview: function() {
            this.configPreview.text(JSON.stringify(_.omit(this.viewOptions, ['el', '_sourceElement']), null, '\t'));
        },

        /**
         * @renderViewViaMethod
         * @private
         */
        renderViewViaMethod: function(method) {
            this.subview(this.constructorName)[method]();
        },

        /**
         * @createPlayground Create playground view
         */
        createPlayground: function() {
            this.$el.find(this.playgroundPropsSelector).append(this.template({
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

                return prop;
            });
        },

        /**
         * @_getViewOptions Get resolved options
         * @private
         */
        _getViewOptions: function(options) {
            options = _.mapObject(_.clone(options), function(prop) {
                if (_.isObject(prop)) {
                    prop = prop.value;
                }
                return prop;
            });

            return options;
        },

        /**
         * @_onChangeProps Handled change field of view options
         *
         * @param {jQuery.Event} event
         * @private
         */
        _onChangeProps: function(event) {
            var $target = $(event.target);
            var name = $target.data('name');
            var value = $target.is(':checkbox') ? $target.is(':checked') : $target.val();

            if ($target.attr('type') === 'number') {
                value = parseFloat(value);
            }

            if ($target.data('template')) {
                value = _.template(value);
            }

            this._setBindOption(name.split('.'), value, this.viewOptions);

            this.disposeView();
            this.createView(this.viewConstructor);
            this.updateConfigPreview();
        },

        /**
         * @_setBindOption set or create nested options values
         *
         * @param {Array} names
         * @param {*} value
         * @param {Object} options
         * @returns {*}
         * @private
         */
        _setBindOption: function(names, value, options) {
            var index = _.first(names);
            if (names.length === 1) {
                options[index] = value;
                return;
            }
            if (_.isUndefined(options[index])) {
                options[index] = {};
            }

            var _rest = _.rest(names);
            var option = options[index];

            if (_rest.length > 1) {
                return this._setBindOption(_rest, value, option);
            }

            option[_.first(_rest)] = value;
        }
    });

    return StyleBookPlayground;
});

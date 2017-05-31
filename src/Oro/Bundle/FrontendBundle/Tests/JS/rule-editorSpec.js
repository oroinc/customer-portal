define(function(require) {
    'use strict';

    var $ = require('jquery');
    var _ = require('underscore');
    var RuleEditor = require('orofrontend/default/js/app/components/rule-editor-component2');
    var initialOptions = JSON.parse(require('text!./Fixture/initial-rule-editor-options.json'));
    var $el = null;
    var html = '<textarea id="test"></textarea>';
    var keyupEvent = null;
    var ruleEditor = null;

    describe('orofrontend/default/js/app/components/rule-editor-component', function() {
        beforeEach(function(done) {
            window.jasmine.DEFAULT_TIMEOUT_INTERVAL = 10000;
            window.setFixtures(html);
            $el = $('#test');

            keyupEvent = $.Event('keyup');
            keyupEvent.keyCode = 13;

            ruleEditor = new RuleEditor(_.extend({}, {
                _sourceElement: $el,
                view: 'orofrontend/default/js/app/views/rule-editor-view2'
            }, initialOptions[0]));

            setTimeout(function() {
                done();
            }, 400);
        });

        afterEach(function() {
            $el = null;
            ruleEditor.strings.childSeparator = '.';
            ruleEditor.dispose();
            ruleEditor = null;
        });

        it('component is defined', function() {
            expect(ruleEditor).toBeDefined();
        });

        it('view is defined', function() {
            expect(ruleEditor.view).toBeDefined();
        });

        describe('check value update after inserting selected value', function() {

            it('inserting in the field start', function(done) {
                $el.val('pro');
                $el.get(0).selectionStart = 2;

                var typeahead = $el.data('typeahead');
                typeahead.lookup();
                typeahead.keyup(keyupEvent);
                expect($el.val()).toEqual('product.');
                done();
            });

            it('inserting in the middle of field', function(done) {
                $el.val('product. == 10');

                $el.get(0).selectionStart = 8;
                $el.get(0).selectionEnd = 8;

                var typeahead = $el.data('typeahead');
                typeahead.lookup();
                typeahead.keyup(keyupEvent);
                expect($el.val()).toEqual('product.featured  == 10');
                done();
            });

            it('inserting in the middle of field selected area', function(done) {
                $el.val('product.id == 10');

                $el.get(0).selectionStart = 8;
                $el.get(0).selectionEnd = 10;

                var typeahead = $el.data('typeahead');
                typeahead.lookup();
                typeahead.keyup(keyupEvent);
                expect($el.val()).toEqual('product.id  == 10');
                done();
            });

            it('inserting in the field end', function(done) {
                $el.val('product.id !');

                $el.get(0).selectionStart = 12;
                $el.get(0).selectionEnd = 12;

                var typeahead = $el.data('typeahead');
                typeahead.lookup();
                typeahead.keyup(keyupEvent);
                expect($el.val()).toEqual('product.id != ');
                done();
            });
        });

        describe('check autocomplete prepare options', function() {

            it('entities', function(done) {

                var expected = {
                    'product': {
                        'child': {
                            'featured': {
                                'label': 'Is Featured',
                                'type': 'boolean',
                                'parentItem': 'product',
                                'group': 'entities',
                                'item': 'product.featured'
                            },
                            'id': {
                                'label': 'Id',
                                'type': 'integer',
                                'parentItem': 'product',
                                'group': 'entities',
                                'item': 'product.id'
                            },
                            'inventory_status': {
                                'label': 'Inventory Status',
                                'type': 'enum',
                                'parentItem': 'product',
                                'group': 'entities',
                                'item': 'product.inventory_status'
                            },
                            'sku': {
                                'label': 'SKU',
                                'type': 'string',
                                'parentItem': 'product',
                                'group': 'entities',
                                'item': 'product.sku'
                            },
                            'map': {
                                'label': 'MAP',
                                'type': 'relation',
                                'relation_alias': 'PriceAttributeProductPrice',
                                'parentItem': 'product',
                                'child': {
                                    'currency': {
                                        'label': 'Currency',
                                        'type': 'string',
                                        'parentItem': 'product.map',
                                        'group': 'entities',
                                        'item': 'product.map.currency'
                                    },
                                    'id': {
                                        'label': 'Id',
                                        'type': 'integer',
                                        'parentItem': 'product.map',
                                        'group': 'entities',
                                        'item': 'product.map.id'
                                    },
                                    'productSku': {
                                        'label': 'Product SKU',
                                        'type': 'string',
                                        'parentItem': 'product.map',
                                        'group': 'entities',
                                        'item': 'product.map.productSku'
                                    },
                                    'quantity': {
                                        'label': 'Quantity',
                                        'type': 'float',
                                        'parentItem': 'product.map',
                                        'group': 'entities',
                                        'item': 'product.map.quantity'
                                    },
                                    'value': {
                                        'label': 'Value',
                                        'type': 'float',
                                        'parentItem': 'product.map',
                                        'group': 'entities',
                                        'item': 'product.map.value'
                                    }
                                },
                                'group': 'entities',
                                'item': 'product.map'
                            },
                            'category': {
                                'label': 'Category',
                                'type': 'relation',
                                'relation_alias': 'Category',
                                'parentItem': 'product',
                                'child': {
                                    'id': {
                                        'label': 'ID',
                                        'type': 'integer',
                                        'parentItem': 'product.category',
                                        'group': 'entities',
                                        'item': 'product.category.id'
                                    },
                                    'left': {
                                        'label': 'Tree left index',
                                        'type': 'integer',
                                        'parentItem': 'product.category',
                                        'group': 'entities',
                                        'item': 'product.category.left'
                                    },
                                    'level': {
                                        'label': 'Tree level',
                                        'type': 'integer',
                                        'parentItem': 'product.category',
                                        'group': 'entities',
                                        'item': 'product.category.level'
                                    },
                                    'materializedPath': {
                                        'label': 'Materialized path',
                                        'type': 'string',
                                        'parentItem': 'product.category',
                                        'group': 'entities',
                                        'item': 'product.category.materializedPath'
                                    },
                                    'right': {
                                        'label': 'Tree right index',
                                        'type': 'integer',
                                        'parentItem': 'product.category',
                                        'group': 'entities',
                                        'item': 'product.category.right'
                                    },
                                    'root': {
                                        'label': 'Tree root',
                                        'type': 'integer',
                                        'parentItem': 'product.category',
                                        'group': 'entities',
                                        'item': 'product.category.root'
                                    },
                                    'createdAt': {
                                        'label': 'Created At',
                                        'type': 'datetime',
                                        'parentItem': 'product.category',
                                        'group': 'entities',
                                        'item': 'product.category.createdAt'
                                    },
                                    'updatedAt': {
                                        'label': 'Updated At',
                                        'type': 'datetime',
                                        'parentItem': 'product.category',
                                        'group': 'entities',
                                        'item': 'product.category.updatedAt'
                                    }
                                },
                                'group': 'entities',
                                'item': 'product.category'
                            }
                        },
                        'group': 'entities',
                        'item': 'product'
                    }
                };

                ruleEditor.options.entities.root_entities = {
                    'Product': 'product'
                };
                ruleEditor.options.entities.fields_data = {
                    'Product': {
                        'featured': {
                            'label': 'Is Featured',
                            'type': 'boolean'
                        },
                        'id': {
                            'label': 'Id',
                            'type': 'integer'
                        },
                        'inventory_status': {
                            'label': 'Inventory Status',
                            'type': 'enum'
                        },
                        'sku': {
                            'label': 'SKU',
                            'type': 'string'
                        },
                        'map': {
                            'label': 'MAP',
                            'type': 'relation',
                            'relation_alias': 'PriceAttributeProductPrice'
                        },
                        'category': {
                            'label': 'Category',
                            'type': 'relation',
                            'relation_alias': 'Category'
                        }
                    },
                    'PriceAttributeProductPrice': {
                        'currency': {
                            'label': 'Currency',
                            'type': 'string'
                        },
                        'id': {
                            'label': 'Id',
                            'type': 'integer'
                        },
                        'productSku': {
                            'label': 'Product SKU',
                            'type': 'string'
                        },
                        'quantity': {
                            'label': 'Quantity',
                            'type': 'float'
                        },
                        'value': {
                            'label': 'Value',
                            'type': 'float'
                        }
                    },
                    'Category': {
                        'id': {
                            'label': 'ID',
                            'type': 'integer'
                        },
                        'left': {
                            'label': 'Tree left index',
                            'type': 'integer'
                        },
                        'level': {
                            'label': 'Tree level',
                            'type': 'integer'
                        },
                        'materializedPath': {
                            'label': 'Materialized path',
                            'type': 'string'
                        },
                        'right': {
                            'label': 'Tree right index',
                            'type': 'integer'
                        },
                        'root': {
                            'label': 'Tree root',
                            'type': 'integer'
                        },
                        'createdAt': {
                            'label': 'Created At',
                            'type': 'datetime'
                        },
                        'updatedAt': {
                            'label': 'Updated At',
                            'type': 'datetime'
                        }
                    }
                };

                ruleEditor._prepareAutocomplete();
                setTimeout(function() {
                    expect(_.isEqual(ruleEditor.entitiesItems, expected)).toEqual(true);
                    done();
                }, 1);
            });

            it('operators filtered', function(done) {

                ruleEditor.options.operations = {
                    math: ['+', '-', '%', '*', '/'],
                };

                ruleEditor._prepareAutocomplete();

                setTimeout(function() {
                    expect(_.isEqual(ruleEditor.operationsItems, {
                        '%': {
                            group: 'operations',
                            item: '%',
                            type: 'math'
                        },
                        '*': {
                            group: 'operations',
                            item: '*',
                            type: 'math'
                        },
                        '+': {
                            group: 'operations',
                            item: '+',
                            type: 'math'
                        },
                        '-': {
                            group: 'operations',
                            item: '-',
                            type: 'math'
                        },
                        '/': {
                            group: 'operations',
                            item: '/',
                            type: 'math'
                        }
                    })).toEqual(true);

                    done();
                }, 1);
            });

            it('check allowed methods', function(done) {
                ruleEditor.options.allowedOperations = ['math', 'like'];

                ruleEditor._prepareAutocomplete();

                setTimeout(function() {
                    expect(_.isEqual(ruleEditor.operationsItems, {
                        '%': {
                            group: 'operations',
                            item: '%',
                            type: 'math'
                        },
                        '*': {
                            group: 'operations',
                            item: '*',
                            type: 'math'
                        },
                        '+': {
                            group: 'operations',
                            item: '+',
                            type: 'math'
                        },
                        '-': {
                            group: 'operations',
                            item: '-',
                            type: 'math'
                        },
                        '/': {
                            group: 'operations',
                            item: '/',
                            type: 'math'
                        },
                        'matches': {
                            group: 'operations',
                            item: 'matches',
                            type: 'like'
                        }
                    })).toEqual(true);

                    done();
                }, 1);
            });

            it('change default child separator', function(done) {

                ruleEditor.strings.childSeparator = ',';

                $el.val('');

                var typeahead = $el.data('typeahead');
                typeahead.lookup();
                typeahead.keyup(keyupEvent);
                expect($el.val()).toEqual('product,');
                done();
            });

        });
    });
});

define(function(require) {
    'use strict';

    require('jasmine-jquery');
    require('orofrontend/default/js/app/views/rule-editor-view');
    var $ = require('jquery');
    var _ = require('underscore');
    var RuleEditor = require('orofrontend/default/js/app/components/rule-editor-component');
    var initialOptions = JSON.parse(require('text!./Fixture/initial-rule-editor-options.json'));
    var $el = null;
    var html = '<textarea id="test"></textarea>';
    var typeahead = null;
    var keyupEvent = null;
    var ruleEditor = null;

    describe('orofrontend/default/js/app/components/rule-editor-component', function() {

        beforeEach(function(done) {
            window.setFixtures(html);
            $el = $('#test');

            keyupEvent = $.Event('keyup');
            keyupEvent.keyCode = 13;

            ruleEditor = new RuleEditor($.extend(true, {}, {
                _sourceElement: $el
            }, initialOptions[0]));

            var getTypeahead = function() {
                typeahead = $el.data('typeahead');
                done();
            };

            if (ruleEditor.deferredInit) {
                ruleEditor.deferredInit.done(getTypeahead);
            } else {
                getTypeahead();
            }
        });

        afterEach(function(done) {
            $el = null;
            ruleEditor.strings.childSeparator = '.';
            ruleEditor.dispose();
            ruleEditor = null;
            typeahead = null;

            done();
        });

        it('component is defined', function() {
            expect(ruleEditor).toBeDefined();
        });

        it('view is defined', function() {
            expect(ruleEditor.view).toBeDefined();
        });

        describe('check rule editor validation', function() {

            it('should be valid when "product.id == 5"', function() {
                expect(ruleEditor.isValid('product.id == 5')).toBeTruthy();
            });

            it('should be not valid when "product."', function() {
                expect(ruleEditor.isValid('product.')).toBeFalsy();
            });

            it('should be not valid when "(product.id == 5 and product.id == 10("', function() {
                expect(ruleEditor.isValid('(product.id == 5 and product.id == 10(')).toBeFalsy();
            });

            it('should be not valid when "(product.id == 5 and product.id == 10()"', function() {
                expect(ruleEditor.isValid('(product.id == 5 and product.id == 10()')).toBeFalsy();
            });

            it('should be not valid when "product"', function() {
                expect(ruleEditor.isValid('product')).toBeFalsy();
            });

            it('window exploid should be not valid when "window.category = {id: 1}; true and category.id"',
                function() {
                    expect(ruleEditor.isValid('window.category = {id: 1}; true and category.id')).toBeFalsy();
                }
            );

            it('should be not valid when "(product.id == 5((((  and product.id == 10()"', function() {
                expect(ruleEditor.isValid('(product.id == 5((((  and product.id == 10()')).toBeFalsy();
            });

            it('should be not valid when ")product.id == 5 and product.id == 10("', function() {
                expect(ruleEditor.isValid(')product.id == 5 and product.id == 10(')).toBeFalsy();
            });

            it('should be not valid when "(product.id == 5() and product.id == 10)"', function() {
                expect(ruleEditor.isValid('(product.id == 5() and product.id == 10)')).toBeFalsy();
            });

            it('should be not valid when "{product.id == 5 and product.id == 10}"', function() {
                expect(ruleEditor.isValid('{product.id == 5 and product.id == 10}')).toBeFalsy();
            });

            it('should be valid when "(product.id == 5 and product.id == 10) or ' +
                '(product.sku in ["sku1", "sku2", "sku3"])"',
                function() {
                    expect(
                        ruleEditor.isValid(
                            '(product.id == 5 and product.id == 10) or (product.sku in ["sku1", "sku2", "sku3"])'
                        )
                    ).toBeTruthy();
                }
            );

            it('should be valid when "product.id == 5" is integer', function() {
                expect(ruleEditor.isValid('product.id == 5')).toBeTruthy();
            });

            it('should be valid when "product.attributeFamily.code == "testStr"" is string', function() {
                expect(ruleEditor.isValid('product.attributeFamily.code == "testStr"')).toBeTruthy();
            });

            it('should be valid when "product.id == 1.234"', function() {
                expect(ruleEditor.isValid('product.id == 1.234')).toBeTruthy();
            });

            it('should be not valid when "product.category.updatedAt > "', function() {
                expect(ruleEditor.isValid('product.category.updatedAt > ')).toBeFalsy();
            });

            it('should be valid when "product.id in [1, 2, 3, 4, 5]"', function() {
                expect(ruleEditor.isValid('product.id in [1, 2, 3, 4, 5]')).toBeTruthy();
            });

            it('should be valid when "product.id matches [1,2,3,4,5]"', function() {
                expect(ruleEditor.isValid('product.id matches [1,2,3,4,5]')).toBeFalsy();
            });

            it('should be valid when "product.id not in [1, 2, 3, 4, 5]"', function() {
                expect(ruleEditor.isValid('product.id not in [1, 2, 3, 4, 5]')).toBeTruthy();
            });

            it('should be valid when "product.id == 2 and product.category.id == category.id"', function() {
                expect(ruleEditor.isValid('product.id == 2 and product.category.id == category.id')).toBeFalsy();
            });

            it('should be valid when "product.id == product.id"', function() {
                expect(ruleEditor.isValid('product.id == product.id')).toBeTruthy();
                expect(ruleEditor.isValid('product.id != product.id')).toBeTruthy();
                expect(ruleEditor.isValid('product.id > product.id')).toBeTruthy();
                expect(ruleEditor.isValid('product.featured < product.featured')).toBeTruthy();
                expect(ruleEditor.isValid('product.sku == product.sku')).toBeTruthy();
                expect(ruleEditor.isValid('product.sku != product.sku')).toBeTruthy();
                expect(ruleEditor.isValid('product.sku > product.sku')).toBeTruthy();
            });

            it('should be not valid when "product.someStr == 4" is not contains at entities or operators', function() {
                expect(ruleEditor.isValid('product.someStr == 4')).toBeFalsy();
                expect(ruleEditor.isValid('someStr == 4')).toBeFalsy();
                expect(ruleEditor.isValid('product.someStr $ 4')).toBeFalsy();
                expect(ruleEditor.isValid('product.sku match test')).toBeFalsy();
            });

            it('should not be valid when "pricelist"', function() {
                expect(ruleEditor.isValid('pricelist')).toBeFalsy();
            });

            it('should not be valid when "pricelist."', function() {
                expect(ruleEditor.isValid('pricelist.')).toBeFalsy();
            });

            it('should not be valid when "pricelist.id"', function() {
                expect(ruleEditor.isValid('pricelist.id')).toBeFalsy();
            });

            it('should not be valid when "pricelist[]"', function() {
                expect(ruleEditor.isValid('pricelist[]')).toBeFalsy();
            });

            it('should not be valid when "pricelist[]."', function() {
                expect(ruleEditor.isValid('pricelist[].')).toBeFalsy();
            });

            it('should not be valid when "pricelist[].id"', function() {
                expect(ruleEditor.isValid('pricelist[].id')).toBeFalsy();
            });

            it('should not be valid when "pricelist[1]"', function() {
                expect(ruleEditor.isValid('pricelist[1]')).toBeFalsy();
            });

            it('should not be valid when "pricelist[1]."', function() {
                expect(ruleEditor.isValid('pricelist[1].')).toBeFalsy();
            });

            it('should be valid when "pricelist[1].id"', function() {
                expect(ruleEditor.isValid('pricelist[1].id')).toBeTruthy();
            });

            it('should be valid when "pricelist[1].prices.value == 1.234"', function() {
                expect(ruleEditor.isValid('pricelist[1].prices.value == 1.234')).toBeTruthy();
            });
        });

        describe('check autocomplete logic', function() {

            it('chain select', function(done) {
                $el.val('');

                typeahead.lookup();

                function iterateSelect(index) {
                    setTimeout(function() { typeahead.select(); }, index * 20);
                }

                for (var i = 0; i < 6; i++) {
                    iterateSelect(i);
                }

                setTimeout(function() {
                    expect($el.val()).toEqual('product.featured + product.featured + ');
                    done();
                }, 200);
            });

            it(':check items resolve if value "product."', function(done) {
                $el.val('product.');
                $el.get(0).selectionStart = 8;
                setTimeout(function() {
                    typeahead.lookup();
                }, 1);

                setTimeout(function() {
                    expect(ruleEditor.view.autocompleteData.items).toEqual(jasmine.objectContaining({
                        'featured': {
                            group: 'entities',
                            item: 'product.featured',
                            label: 'Is Featured',
                            parentItem: 'product',
                            type: 'boolean'
                        },
                        'id': {
                            group: 'entities',
                            item: 'product.id',
                            label: 'Id',
                            parentItem: 'product',
                            type: 'integer'
                        },
                        'sku': {
                            group: 'entities',
                            item: 'product.sku',
                            label: 'SKU',
                            parentItem: 'product',
                            type: 'string'
                        },
                        'status': {
                            group: 'entities',
                            item: 'product.status',
                            label: 'Status',
                            parentItem: 'product',
                            type: 'string'
                        }
                    }));
                    done();
                }, 40);

            });

            it(':check items resolve if previous item is entity or scalar(not operation)', function(done) {

                var values = ['product.featured', '1', '1 in [1,2,3]', '(1 == 1)'];
                _.each(values, function(value) {
                    $el.val(value + ' ');
                    $el.get(0).selectionStart = $el.val().length;

                    typeahead.lookup();

                    expect(ruleEditor.view.autocompleteData.items).toEqual(jasmine.objectContaining({
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
                        '==': {
                            group: 'operations',
                            item: '==',
                            type: 'equality'
                        }
                    }));
                });
                done();
            });

            it(':check items autocomplete group if previous character is "("', function(done) {
                $el.val('( ');

                typeahead.lookup();

                expect(ruleEditor.view.autocompleteData.items.product).toBeDefined();
                expect(ruleEditor.view.autocompleteData.items.pricelist).toBeDefined();

                done();
            });

            it('check level 1', function() {
                ruleEditor.options.termLevelLimit = 1;
                ruleEditor._prepareAutocomplete();

                expect(ruleEditor.entitiesItems).toBeDefined();
                expect(ruleEditor.entitiesItems).toEqual({});
            });

            it('check level 2', function() {
                ruleEditor.options.termLevelLimit = 2;
                ruleEditor._prepareAutocomplete();

                var expected = {
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
                    'status': {
                        'label': 'Status',
                        'type': 'string',
                        'parentItem': 'product',
                        'group': 'entities',
                        'item': 'product.status'
                    },
                    'type': {
                        'label': 'Type',
                        'type': 'string',
                        'parentItem': 'product',
                        'group': 'entities',
                        'item': 'product.type'
                    },
                    'createdAt': {
                        'label': 'Created At',
                        'type': 'datetime',
                        'parentItem': 'product',
                        'group': 'entities',
                        'item': 'product.createdAt'
                    },
                    'updatedAt': {
                        'label': 'Updated At',
                        'type': 'datetime',
                        'parentItem': 'product',
                        'group': 'entities',
                        'item': 'product.updatedAt'
                    }
                };

                expect(ruleEditor.entitiesItems.product.child).toBeDefined();
                expect(ruleEditor.entitiesItems.product.child).toEqual(jasmine.objectContaining(expected));
            });

            it('check level 3', function() {
                ruleEditor.options.termLevelLimit = 3;
                ruleEditor._prepareAutocomplete();

                var expected = {
                    'currency': {
                        'label': 'Currency',
                        'type': 'string',
                        'parentItem': 'product.msrp',
                        'group': 'entities',
                        'item': 'product.msrp.currency'
                    },
                    'id': {
                        'label': 'Id',
                        'type': 'integer',
                        'parentItem': 'product.msrp',
                        'group': 'entities',
                        'item': 'product.msrp.id'
                    },
                    'productSku': {
                        'label': 'Product SKU',
                        'type': 'string',
                        'parentItem': 'product.msrp',
                        'group': 'entities',
                        'item': 'product.msrp.productSku'
                    },
                    'quantity': {
                        'label': 'Quantity',
                        'type': 'float',
                        'parentItem': 'product.msrp',
                        'group': 'entities',
                        'item': 'product.msrp.quantity'
                    },
                    'value': {
                        'label': 'Value',
                        'type': 'float',
                        'parentItem': 'product.msrp',
                        'group': 'entities',
                        'item': 'product.msrp.value'
                    }
                };
                expect(ruleEditor.entitiesItems.product.child.map.child).toBeDefined();
                expect(ruleEditor.entitiesItems.product.child.map.child).toEqual(jasmine.objectContaining(expected));
            });
        });

        describe('check value update after inserting selected value', function() {

            it('inserting in the field start', function(done) {
                $el.val('pro');
                $el.get(0).selectionStart = 2;

                typeahead.lookup();
                typeahead.select();

                setTimeout(function() {
                    expect($el.val()).toEqual('product.');
                    done();
                }, 100);
            });

            it('inserting in the middle of field', function(done) {
                $el.val('product. == 10');

                $el.get(0).selectionStart = 8;
                $el.get(0).selectionEnd = 8;

                typeahead.lookup();
                typeahead.select();

                setTimeout(function() {
                    expect($el.val()).toEqual('product.featured  == 10');
                    done();
                }, 100);
            });

            it('inserting in the middle of field selected area', function(done) {
                $el.val('product.id == 10');

                $el.get(0).selectionStart = 8;
                $el.get(0).selectionEnd = 10;

                typeahead.lookup();
                typeahead.select();

                setTimeout(function() {
                    expect($el.val()).toEqual('product.id  == 10');
                    done();
                }, 100);
            });

            it('inserting in the field end', function(done) {
                $el.val('product.id !');

                $el.get(0).selectionStart = 12;
                $el.get(0).selectionEnd = 12;

                typeahead.lookup();
                typeahead.select();

                setTimeout(function() {
                    expect($el.val()).toEqual('product.id != ');
                    done();
                }, 100);
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

                typeahead.lookup();
                typeahead.select();

                setTimeout(function() {
                    expect($el.val()).toEqual('product,');
                    done();
                }, 50);
            });
        });

        describe('check rule editor data source render', function() {

            it('shown if type pricel', function(done) {
                $el.val('pricel');
                typeahead.lookup();
                typeahead.select();

                expect('#data-source-element').toExist();
                done();
            });

            it('remove data source', function(done) {
                $el.val('pricelist[1].id + product.id');
                $el[0].selectionStart = 27;

                typeahead.lookup();

                expect('#data-source-element').not.toExist();
                done();
            });
        });
    });
});

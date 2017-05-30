define(function(require) {
    'use strict';

    var $ = require('jquery');
    var _ = require('underscore');
    var RuleEditor = require('orofrontend/default/js/app/components/rule-editor-component2');
    var initialOptions = JSON.parse(require('text!./Fixture/initial-rule-editor-options.json'));
    var $el = null;
    var keyupEvent = null;

    describe('orofrontend/default/js/app/components/rule-editor-component', function() {
        beforeEach(function(done) {
            $el = $('<textarea></textarea>');
            $('body').append($el);

            keyupEvent = $.Event('keyup');
            keyupEvent.keyCode = 13;

            this.ruleEditor = new RuleEditor(_.extend({}, {
                _sourceElement: $el,
                view: 'orofrontend/default/js/app/views/rule-editor-view2'
            }, initialOptions[0]));

            setTimeout(function() {
                done();
            }, 400);
        });

        afterEach(function() {
            $el.remove();
            $el = null;
            this.ruleEditor.dispose();
            delete this.ruleEditor;
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
    });
});

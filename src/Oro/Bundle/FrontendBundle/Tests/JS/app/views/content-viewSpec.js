define(function(require) {
    'use strict';

    const ContentView = require('orofrontend/js/app/views/page/content-view');
    const $ = require('jquery');

    describe('Content View', function() {
        it('Autofocus on frontend form without data-focusable', function() {
            window.setFixtures([
                '<div id="container">',
                '<form id="form">',
                '<input id="focused" type="text"></input>',
                '</form>',
                '</div>'
            ].join(''));

            const contentView = new ContentView({
                el: '#container'
            });

            contentView.initFocus();

            expect($(document.activeElement).attr('id')).toEqual(undefined);
        });

        it('Autofocus on frontend form with data-focusable', function() {
            window.setFixtures([
                '<div id="container">',
                '<form id="form" data-focusable>',
                '<input id="focused" type="text"></input>',
                '</form>',
                '</div>'
            ].join(''));

            const contentView = new ContentView({
                el: '#container'
            });

            contentView.initFocus();

            expect($(document.activeElement).attr('id')).toEqual('focused');
        });

        it('Autofocus on frontend form with data-focusable and multiple inputs', function() {
            window.setFixtures([
                '<div id="container">',
                '<form id="form" data-focusable>',
                '<input id="focused" type="text"></input>',
                '<input type="text"></input>',
                '<input type="text"></input>',
                '<input type="text"></input>',
                '<input type="text"></input>',
                '</form>',
                '</div>'
            ].join(''));

            const contentView = new ContentView({
                el: '#container'
            });

            contentView.initFocus();

            expect($(document.activeElement).attr('id')).toEqual('focused');
        });

        it('Autofocus on frontend form with data-focusable and without input', function() {
            window.setFixtures([
                '<div id="container">',
                '<form id="form" data-focusable>',
                '</form>',
                '</div>'
            ].join(''));

            const contentView = new ContentView({
                el: '#container'
            });

            contentView.initFocus();

            expect($(document.activeElement).attr('id')).toEqual(undefined);
        });
    });
});

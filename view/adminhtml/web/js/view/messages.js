define([
    'jquery',
    'uiComponent',
    'underscore',
    'escaper',
    'mage/translate',
    'text!./../../template/modal/modal-message.html',
    'jquery/jquery-storageapi',
], function ($, Component, _, escaper, $t, modalMessageTpl) {
    'use strict';

    const COOKIE_NAME = 'mk-popup-messages-admin';
    const KEY_TEXT = 'text';

    return Component.extend({
        defaults: {
            messages: [],
            allowedTags: ['div', 'span', 'b', 'strong', 'i', 'em', 'u', 'a', 'br']
        },

        initialize: function () {
            this._super();

            this.readFromCookie();
            this.show();
            this.clean();
        },

        /**
         * Get messages to display
         */
        readFromCookie: function () {
            this.messages = _.unique($.cookieStorage.get(COOKIE_NAME), KEY_TEXT);
        },

        /**
         * Show popup for each message
         */
        show: function () {
            for (let message of this.messages) {
                this.shopPopup(message);
            }
        },

        /**
         * Remove messages from the cookie to avoid displaying them again
         */
        clean: function () {
            if (true === $.cookieStorage.isSet(COOKIE_NAME)) {
                $.cookieStorage.setPath('/');
                $.cookieStorage.set(COOKIE_NAME, '');
            }
        },

        /**
         * Display single message in popup
         *
         * @param message
         */
        shopPopup: function (message) {
            let type = message.type;
            let title = message.title ? message.title : message.type;
            let text = this.prepareMessageForHtml(message.text);
            let popup = $(`<div></div>`);

            popup.html(text);
            popup.modal({
                popupTpl: modalMessageTpl,
                title: $t(title),
                headerClass: `message message-${type}`,
            });

            popup.modal('openModal');
        },

        /**
         * Prepare the given message to be rendered as HTML
         *
         * @param {String} message
         * @return {String}
         */
        prepareMessageForHtml: function (message) {
            return escaper.escapeHtml(message, this.allowedTags);
        },
    });
});

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

    return Component.extend({
        defaults: {
            fieldSuccess: 'success',
            fieldMessage: 'mk_message',
            fieldMessageType: 'type',
            fieldMessageLabel: 'label',
            fieldMessageText: 'text',
            showOnErrors: true,
            showOnSuccess: true,
            allowedTags: ['div', 'span', 'b', 'strong', 'i', 'em', 'u', 'a', 'br']
        },

        initialize: function () {
            this._super();
            this.subscribeOnResponses();
        },

        /**
         * Watch for AJAX request results
         */
        subscribeOnResponses: function () {
            window.montikidsFlags = window.montikidsFlags ? window.montikidsFlags : {};

            if (true === window.montikidsFlags.ajaxPopupsInitialized) {
                return;
            }

            $(document).on('ajaxComplete ajaxError', $.proxy(this.parseResponse, this));
            window.montikidsFlags.ajaxPopupsInitialized = true;
        },

        /**
         * Add new message
         *
         * @protected
         * @param {Object} event - object
         * @param {Object} jqXHR - The jQuery XMLHttpRequest object returned by $.ajax()
         */
        parseResponse: function (event, jqXHR) {
            try {
                let response = JSON.parse(jqXHR.responseText);

                if (false === _.isEmpty(response)) {
                    let message = response[this.fieldMessage];
                    let isSuccess = response[this.fieldSuccess];

                    if (true === _.isEmpty(message)) {
                        return;
                    }

                    if ((true === isSuccess) && (true === this.showOnSuccess)) {
                        this.shopPopup(message);
                    }

                    if ((false === isSuccess) && (true === this.showOnErrors)) {
                        this.shopPopup(message);
                    }
                }
            } catch (e) {
                // Do nothing, it's okay
            }
        },

        /**
         * @param message
         */
        shopPopup: function (message) {
            let messageText = message[this.fieldMessageText];
            let type = message[this.fieldMessageType] ?? '';
            let text = this.prepareMessageForHtml(messageText);
            let popup = $(`<div></div>`);

            let title = message[this.fieldMessageTitle] ?? type;
            title = this.capitalizeFirstLetter($t(title));

            popup.html(text);
            popup.modal({
                popupTpl: modalMessageTpl,
                title: title,
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

        /**
         * @param text
         * @returns {string}
         */
        capitalizeFirstLetter: function (text) {
            return text.charAt(0).toUpperCase() + text.slice(1);
        },
    });
});

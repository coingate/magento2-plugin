define([
    "jquery",
    "Magento_Ui/js/modal/alert",
    "mage/translate",
    "jquery/ui",
], function ($, alert, $t) {
    "use strict";

    $.widget('merchant.testconnection', {
        options: {
            ajaxUrl: '',
            testConnection: '',
        },
        _create: function () {
            var self = this;

            $(this.options.testConnection).click(function (e) {
                e.preventDefault();
                self._ajaxSubmit();
            });
        },

        _ajaxSubmit: function () {
            $.ajax({
                url: this.options.ajaxUrl,
                data: {
                    form_key: $('input[name="form_key"]').val()
                },
                dataType: 'json',
                showLoader: true,
                success: function (result) {
                    alert({
                        title: result.status ? $t('Success') : $t('Error'),
                        content: result.content
                    });
                }
            });
        }
    });

    return $.merchant.testconnection;
});

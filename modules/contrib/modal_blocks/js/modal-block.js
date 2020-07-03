(function ($, Drupal, drupalSettings) {

    'use strict';

    /**
     * Attaches the JS regarding the modal block.
     */
    Drupal.behaviors.jsmodalblock = {
        attach: function (context, settings) {
            var modal = $('#modal-block');
            var close = $('#modal-block-close')[0];
            var con = modal.parent();
            var parDiv = con.parent();
            $(".modal-block-close").on('click', function () {
                $(".modal").css("display", "none");
                $(".block-modalblock").css("dispaly", "none");
                $(".parDiv").css("display", "none");
            });
        }
    };
})(jQuery, Drupal, drupalSettings);

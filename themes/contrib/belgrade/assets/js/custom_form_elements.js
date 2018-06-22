 /**
 * @file
 * Belgrade Theme JS.
 */
(function ($) {

    'use strict';

    /**
     * Close behaviour.
     */
    Drupal.behaviors.quantityIncDec = {
      attach: function (context, settings) {
        $(".quantity-btn").on("click", function() {

            var $button = $(this);
            var oldValue = parseInt($button.parent().find("input").val());

            if ($button.text() == "+") {
              var newVal = parseInt(oldValue) + 1;
            } else {
              // Don't allow decrementing below zero
              if (oldValue > 0) {
                var newVal = parseInt(oldValue) - 1;
              } else {
                newVal = 0;
              }
            }

            $button.parent().find("input").val(newVal);
          });
      }
    };

  })(jQuery);

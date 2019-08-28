/**
 * @file
 * Drulma js behavior.
 */

(function (Drupal, Bulma) {

  'use strict';

  function once(fn, context) {
    var result;

    return function() {
      if(fn) {
        result = fn.apply(context || this, arguments);
        fn = null;
      }

      return result;
    };
  }

  /**
   * Parse the parts added with ajax.
   */
  Drupal.behaviors.DrulmaJS = {
    attach: function (context, settings) {
      // The BulmaJS library already traverses the DOM on document.
      if (context != document) {
        once(Bulma.traverseDOM(), context);
      }
    }
  };

} (Drupal, Bulma));

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.YasmDatatables = {
    attach: function (context, settings) {
      $('.datatable').dataTable({destroy: true, order: []});
    }
  };
})(jQuery, Drupal);

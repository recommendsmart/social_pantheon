/**
 * @file
 * Processes deletion of new field.
 */
(function ($) {

  Drupal.behaviors.matrix_fields = {
    attach: function (context) {
      $('.delete-item-row').each(function() {
        $(this).click(function(e) {
          e.preventDefault();
          var selector = 'edit-fields-' + $(this).data('id');
          $('[data-drupal-selector="' + selector + '"]').detach();
        });
      });
      $('select.collections-select').each(function() {
        if (!$(this).next().hasClass('ms-parent')) {
          $(this).once('matrix_fields').multipleSelect();
        }
      });
    }
  };

})(jQuery);
/**
 * @file
 * Mailboxes action JS functionality.
 */

(function ($, Drupal) {

  var deselectAll = function() {
    $(".vbo-view-form").find("tr input:checkbox:checked").each(function() {
      $(this).prop('checked', false);
    });
  };

  var selectGroup = function(group) {
    // Use "trigger(click)" not "prop(checked)" to fire "select all" checkbox
    // when needed.
    switch(group) {
      case "none":
        break;

      case "all":
        $(".vbo-view-form tbody tr input:checkbox").each(function() {
          $(this).trigger("click");
        });
        break;

      default:
        $(".vbo-view-form tbody tr." + group + " input:checkbox.form-checkbox").each(function() {
          $(this).trigger("click");
        });
        break;
    }
  };

  Drupal.behaviors.nbox_ui_mailboxes = {
    attach: function (context, settings) {
      $("select#edit-folder", context).on('change', function() {
        deselectAll();
        selectGroup($(this).val());
      });
    }
  };

})(jQuery, Drupal);

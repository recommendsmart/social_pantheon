(function ($) {
  Drupal.behaviors.agerp_core_basic_merge_table = {
    attach: function(context) {
      $('#merge-basics-table tr').each(function() {
        var context = $(this);
        var all_radios = $('input[type=radio]', context);
        $('input[type=radio]:not(.processed)', context).change(function() {
          all_radios.addClass('processed');
          all_radios.not(this).attr('checked', '');
          all_radios.removeClass('processed');
        });
      });
    }
  }
})(jQuery);

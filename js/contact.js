//Contact
jQuery(document).ready(function($) {
  $(".clickable-row").click(function() {
    if ($(this).data("target")) {
      window.open($(this).data("href"), "_blank");
    } else {
      window.location = $(this).data("href");
    }
  });
});

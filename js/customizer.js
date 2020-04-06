(function ($) {
  "use strict";

  wp.customize("cp_display_authors", function (setting) {
    setting.bind(function (to) {
      false === to ? $(".post__author").hide() : $(".post__author").show();
    });
  });
})(jQuery);

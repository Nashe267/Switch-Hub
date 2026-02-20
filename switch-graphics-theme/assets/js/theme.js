(function ($) {
	"use strict";

	$(function () {
		var $toggle = $(".menu-toggle");
		var $navigation = $("#site-navigation");

		if (!$toggle.length || !$navigation.length) {
			return;
		}

		$toggle.on("click", function () {
			var expanded = $toggle.attr("aria-expanded") === "true";
			$toggle.attr("aria-expanded", expanded ? "false" : "true");
			$navigation.toggleClass("toggled");
		});

		$(window).on("resize", function () {
			if (window.innerWidth > 860) {
				$navigation.removeClass("toggled");
				$toggle.attr("aria-expanded", "false");
			}
		});
	});
})(jQuery);

(function ($) {
	"use strict";

	$(function () {
		var $toggle = $(".menu-toggle");
		var $navigation = $("#site-navigation");
		var $menuClose = $(".menu-close");
		var $menuBackdrop = $navigation.find(".menu-backdrop");
		var openClass = "is-open";
		var bodyOpenClass = "sgt-menu-open";

		if (!$toggle.length || !$navigation.length) {
			return;
		}

		function openMenu() {
			$navigation.addClass(openClass);
			$("body").addClass(bodyOpenClass);
			$toggle.attr("aria-expanded", "true");
		}

		function closeMenu() {
			$navigation.removeClass(openClass);
			$("body").removeClass(bodyOpenClass);
			$toggle.attr("aria-expanded", "false");
		}

		$toggle.on("click", function () {
			if ($navigation.hasClass(openClass)) {
				closeMenu();
				return;
			}

			openMenu();
		});

		$menuClose.on("click", closeMenu);
		$menuBackdrop.on("click", closeMenu);

		$navigation.find("a").on("click", function () {
			closeMenu();
		});

		$(document).on("keyup", function (event) {
			if (event.key === "Escape") {
				closeMenu();
			}
		});

		$(window).on("resize", function () {
			if (window.innerWidth > 1200) {
				closeMenu();
			}
		});
	});
})(jQuery);

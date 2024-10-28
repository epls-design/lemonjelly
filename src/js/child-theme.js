(function ($) {
	let navbarCollapse = 1200; // Set in the parent theme

	function toggleNavbarClass(status = "on") {
		if (status === "off") {
			$("body").removeClass("has-transparent-navbar");
			$("body").attr("data-transparent-navbar", "true");
		} else {
			$("body").addClass("has-transparent-navbar");
			$("body").attr("data-transparent-navbar", "false");
		}
	}

	function reInitNavbar() {
		let navBarHeight = $("#masthead").outerHeight();
		let currentScrollPosition = $(window).scrollTop();

		$("body").css("--hero-padding", navBarHeight + "px");

		// If window is smaller than navbarCollapse, remove '.has-transparent-navbar' from body, move it to a data-attribute
		if ($(window).width() <= navbarCollapse) {
			toggleNavbarClass("off");
			return;
		} else if (currentScrollPosition > navBarHeight * 0.5) {
			toggleNavbarClass("off");
		} else {
			toggleNavbarClass("on");
		}
	}

	$(document).on("mouseenter", "#masthead", function () {
		toggleNavbarClass("off");
	});

	$(document).on("mouseleave", "#masthead", function () {
		reInitNavbar();
	});

	$(document).ready(function () {
		reInitNavbar();
	});

	// Check if jfDebounce is available
	if (typeof jfDebounce === "function") {
		jfDebounce("resize", reInitNavbar, 100);
		jfDebounce("scroll", reInitNavbar, 50);
	}
})(jQuery);

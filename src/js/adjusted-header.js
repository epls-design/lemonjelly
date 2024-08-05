//Hero Video section
(function ($) {
	function adjustBodyPadding() {
		if ($(".alternate-header").length) {
			var headerHeight = $(".alternate-header .main-navigation").outerHeight();
			$("body").css("padding-top", headerHeight + "px");
		}
	}

	$(document).ready(function () {
		adjustBodyPadding();
		$(window).resize(function () {
			adjustBodyPadding();
		});
	});
})(jQuery);

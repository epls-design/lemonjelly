//Hero Video section
(function ($) {
	function minHeightVideoHero() {
		// Does the document have a .video-header
		if ($(".video-header").length) {
			// Get outerHeight of #site-navigation
			const navHeight = $("#site-navigation").outerHeight();

			$(".video-header")
				.css("min-height", `calc(75vh - ${navHeight}px)`)
				.css("min-height", `calc(75dvh - ${navHeight}px)`);
		}
	}

	// Initialize on load
	$(document).ready(function () {
		minHeightVideoHero();
	});

	// Reinitialize on resize
	if (typeof lemonjellyDebounce !== "undefined") {
		const reinitVideoHero = lemonjellyDebounce(() => minHeightVideoHero());
		window.addEventListener("resize", reinitVideoHero);
	}

	$(document).on("click", ".play-button", function () {
		const block = $(this).closest("header");
		const fullVideo = block.find(".hero-video-full");
		const previewVideo = block.find("video");
		const container = block.find(".container");
		const heroVideoOverlay = block.find(".hero-video-overlay");

		if (!block.hasClass("is-playing")) {
			previewVideo.hide();
			container.hide();
			heroVideoOverlay.hide();
			fullVideo.show();
			$(".play-button").hide();

			// Click the .play button
			fullVideo.find(".play").click();
		}

		// Toggle Class 'is-playing' on nearest <header> element
		block.toggleClass("is-playing");

		// unFocus
		$(this).blur();
	});
})(jQuery);

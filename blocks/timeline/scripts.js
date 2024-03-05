// Note: These should match the values set in scss. Currently it is a manual process to keep them in sync

// Simple JS Debounce function  https://www.freecodecamp.org/news/javascript-debounce-example/
let lastTimeLineHeight = "";
(function ($) {
	function repositionTimeLineElements() {
		console.log("working");
		if ($(window).width() >= breakPoints.md) {
			lastTimeLineHeight = "";
			let timelines = $(".timeline");
			if (timelines.length > 0) {
				timelines.each(function () {
					// Get all .time-container elements
					let timeContainers = $(this).find(".time-container");
					if (timeContainers.length > 0) {
						timeContainers.each(function () {
							if (lastTimeLineHeight !== "") {
								$(this).css("margin-top", -lastTimeLineHeight / 2 + "px");
							}
							lastTimeLineHeight = $(this).height();
						});
					}
				});
			}
		} else {
			$(".time-container").css("margin-top", "");
		}
	}

	$(document).on("ready", function () {
		repositionTimeLineElements();
	});

	jfDebounce("resize", repositionTimeLineElements);
})(jQuery);

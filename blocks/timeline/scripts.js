let lastTimeLineHeight = "";
let lastLeftHeight = "";
let lastRightHeight = "";
(function ($) {
	function repositionTimeLineElements() {
		if ($(window).width() >= 900) {
			lastTimeLineHeight = "";
			let timelines = $(".timeline");
			if (timelines.length > 0) {
				timelines.each(function () {
					// Get all .time-container elements
					let timeContainers = $(this).find(".time-container");
					if (timeContainers.length > 0) {
						timeContainers.each(function () {
							let timelineSide = $(this).hasClass("left") ? "left" : "right";

							if (lastTimeLineHeight !== "") {
								let offSetBy = 0;

								if (timelineSide == "left") {
									let diff = lastLeftHeight - lastRightHeight;
									let rightSideOffset = lastLeftHeight / 2;

									if (diff - rightSideOffset > 0) {
										// The difference is greater than the right side offset - we need to add
										offSetBy = diff - rightSideOffset;
									} else {
										//The difference is less than the right side offset - we need to subtract
										offSetBy = ((rightSideOffset - diff) * -1) / 2;
									}
								} else {
									let diff = lastRightHeight - lastLeftHeight;
									let leftSideOffset = lastRightHeight / 2;

									if (diff - leftSideOffset > 0) {
										// The difference is greater than the right side offset - we need to add
										offSetBy = diff - leftSideOffset;
									} else {
										//The difference is less than the right side offset - we need to subtract
										offSetBy = ((leftSideOffset - diff) * -1) / 2;
									}
								}

								$(this).css("margin-top", offSetBy + "px");
							}
							lastTimeLineHeight = $(this).outerHeight();

							if ($(this).hasClass("left")) {
								lastLeftHeight = $(this).outerHeight();
							} else {
								lastRightHeight = $(this).outerHeight();
							}
						});
					}
				});
			}
		} else {
			$(".time-container").css("margin-top", "");
		}
	}

	// Check if jfDebounce is available
	if (typeof jfDebounce === "function") {
		jfDebounce("resize", repositionTimeLineElements, 20);
	}

	if (window.acf) {
		window.acf.addAction(
			"render_block_preview/type=ezpz/timeline",
			function () {
				repositionTimeLineElements();
			}
		);
	} else {
		document.addEventListener("DOMContentLoaded", function () {
			repositionTimeLineElements();
		});
	}
})(jQuery);

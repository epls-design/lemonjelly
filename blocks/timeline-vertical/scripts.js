(function ($) {
	$(document).ready(function () {
		// Check if .block-timeline exists. If not, exit the script to prevent errors.
		if (!$(".block-vertical-timeline").length) {
			return;
		}

		// Cached jQuery selectors for better performance
		var $timelineWrapper = $(".timeline-wrapper");
		var $timelineContainer = $(".timeline-container");
		var $topTimelineContainer = $(".top-timeline-container");
		var $container = $(".container");
		var $timelineScrollContainer = $(".timeline-scroll-container");
		var $timelineScroll = $(".timeline-scroll");
		var $scrollYears = $(".scroll-years");
		var $scrollYear = $(".scroll-year .scroll-year-text");
		var $timelineItems = $(".timeline-item");

		// Variables to handle dragging and scrolling
		var isDraggingTimeline = false;
		var isDraggingScrollYear = false;
		var startX, scrollLeft;
		var lastScrollLeft = 0;
		var scrollDirection = "right";
		var lastScrollTime = Date.now();

		/**
		 * Adjusts the width of the timeline container based on the widths of the container and the timeline items.
		 * This ensures the timeline items are properly aligned and spaced.
		 */
		function adjustTimelineContainerWidth() {
			var containerWidth = $container.width();
			var topTimelineWidth = $topTimelineContainer.width();
			var lastTimelineItemWidth = $topTimelineContainer
				.find(".timeline-item")
				.last()
				.width();

			var newTimelineContainerWidth =
				topTimelineWidth + containerWidth - lastTimelineItemWidth;
			$timelineContainer.css("width", newTimelineContainerWidth + "px");
		}

		/**
		 * Highlights the appropriate year based on the current scroll position.
		 * This function updates the year displayed in the scroll bar.
		 */
		function updateHighlightedYear() {
			var scrollLeft = $timelineWrapper.scrollLeft();
			var containerLeft = $timelineScrollContainer.offset().left;
			var activeItem = null;

			// Find the timeline item that is closest to the left edge of the container
			$timelineItems.each(function () {
				var $item = $(this);
				var itemLeft = $item.offset().left;

				if (Math.abs(itemLeft - containerLeft) < 1) {
					activeItem = $item;
					return false; // Break out of the loop when the active item is found
				}
			});

			if (activeItem) {
				var year = activeItem.data("timeline-year");
				$scrollYear.text(year);
			}

			// Calculate the percentage scrolled
			var scrollPercentage =
				scrollLeft /
				($timelineWrapper[0].scrollWidth - $timelineWrapper[0].clientWidth);
			var scrollYearPosition =
				16 + // Offset from the left
				scrollPercentage *
					($timelineScroll[0].scrollWidth -
						$scrollYear.parent().outerWidth() -
						32); // Offset from the right

			// Adjust the position of the scroll-year element
			$scrollYear.parent().css("left", scrollYearPosition + "px");

			// Update scroll direction
			if (scrollLeft > lastScrollLeft) {
				scrollDirection = "right";
			} else if (scrollLeft < lastScrollLeft) {
				scrollDirection = "left";
			}
			lastScrollLeft = scrollLeft;
		}

		/**
		 * Checks and updates the highlighted year based on the scroll position.
		 * This function runs at regular intervals to ensure the year is correctly updated.
		 */
		function checkAndUpdateHighlightedYear() {
			var scrollLeft = $timelineWrapper.scrollLeft();
			var maxScrollLeft =
				$timelineWrapper[0].scrollWidth - $timelineWrapper[0].clientWidth;
			var containerLeft = $timelineScrollContainer.offset().left;
			var activeItem = null;
			var closestDistance = Infinity;

			var lastYear = $(".timeline-year-text.last-year").text();

			// If scrolled to the very end, set the last year
			if (Math.abs(scrollLeft - maxScrollLeft) <= 5) {
				$scrollYear.text(lastYear);
				return;
			}

			// Find the closest timeline item to the left edge of the container
			$timelineItems.each(function () {
				var $item = $(this);
				var itemLeft = $item.offset().left;
				var distance = Math.abs(itemLeft - containerLeft);

				if (
					scrollDirection === "right" &&
					itemLeft <= containerLeft &&
					distance < closestDistance
				) {
					closestDistance = distance;
					activeItem = $item;
				} else if (
					scrollDirection === "left" &&
					itemLeft >= containerLeft &&
					distance < closestDistance
				) {
					closestDistance = distance;
					activeItem = $item;
				}
			});

			if (activeItem) {
				var year = activeItem.data("timeline-year");
				$scrollYear.text(year);
			}
		}

		// Event handler for scrolling in the timeline wrapper
		$timelineWrapper.on("scroll", function () {
			$timelineScroll.scrollLeft($timelineWrapper.scrollLeft());
			updateHighlightedYear();
			lastScrollTime = Date.now();
		});

		// Event handler for scrolling in the timeline scroll bar
		$timelineScroll.on("scroll", function () {
			$timelineWrapper.scrollLeft($timelineScroll.scrollLeft());
			updateHighlightedYear();
			lastScrollTime = Date.now();
		});

		// Mouse drag events for .scroll-year
		$scrollYear.parent().on("mousedown", function (event) {
			isDraggingScrollYear = true;
			startX = event.pageX - $scrollYear.parent().position().left;
			scrollLeft = $timelineWrapper.scrollLeft();
			event.preventDefault(); // Prevent text selection
		});

		// Mouse drag events for .timeline-wrapper
		$timelineWrapper.on("mousedown", function (event) {
			isDraggingTimeline = true;
			startX = event.pageX - $timelineWrapper.offset().left;
			scrollLeft = $timelineWrapper.scrollLeft();
		});

		$(document).on("mouseup", function () {
			isDraggingTimeline = false;
			isDraggingScrollYear = false;
		});

		$(document).on("mousemove", function (event) {
			if (isDraggingScrollYear) {
				event.preventDefault();
				var x = event.pageX - startX;
				var scrollPercentage =
					(x - 16) /
					($timelineScroll.width() - $scrollYear.parent().outerWidth() - 32);
				var newScrollLeft =
					scrollPercentage *
					($timelineWrapper[0].scrollWidth - $timelineWrapper[0].clientWidth);
				$timelineWrapper.scrollLeft(newScrollLeft);
				lastScrollTime = Date.now();
			}

			if (isDraggingTimeline) {
				event.preventDefault();
				var x = event.pageX - $timelineWrapper.offset().left;
				var walk = (x - startX) * 2; // Adjust the multiplier for faster/slower scrolling
				$timelineWrapper.scrollLeft(scrollLeft - walk);
				lastScrollTime = Date.now();
			}
		});

		// Initial adjustment and highlighting
		adjustTimelineContainerWidth();
		updateHighlightedYear();

		// Adjust the width on window resize
		$(window).on("resize", function () {
			adjustTimelineContainerWidth();
			updateHighlightedYear();
		});

		// Check the highlighted year every 0.25 seconds if there's been scroll activity in the last 2 seconds
		setInterval(function () {
			if (Date.now() - lastScrollTime <= 2000) {
				checkAndUpdateHighlightedYear();
			}
		}, 250);
	});
})(jQuery);

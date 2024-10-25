(function ($) {
	function initTwentyTwenty() {
		let twentyTwenty = $(".twentytwenty-container");
		if (twentyTwenty.length) {
			twentyTwenty.each(function () {
				console.log($(this).data("before-label"));
				let beforeLabel = $(this).data("before-label")
					? $(this).data("before-label")
					: "Before";

				alert(beforeLabel);
				let afterLabel = $(this).data("after-label")
					? $(this).data("after-label")
					: "After";

				$opts = {
					before_label: beforeLabel,
					after_label: afterLabel,
				};

				$(this).twentytwenty($opts);
			});
		}
	}

	if (window.acf) {
		window.acf.addAction(
			"render_block_preview/type=ezpz/image-compare",
			function () {
				initTwentyTwenty();
			}
		);
	} else {
		document.addEventListener("DOMContentLoaded", function () {
			initTwentyTwenty();
		});
	}
})(jQuery);

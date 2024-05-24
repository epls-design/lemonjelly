(function ($) {
	/**
	 * Initialises a single feedback map using the Google Maps Javascript API
	 * @param {*} $map - jQuery object of the map element
	 * @returns {google.maps.Map} - The map object
	 */
	function initialiseFeedbackMap($map) {
		let map;

		async function initMap() {
			const { Map } = await google.maps.importLibrary("maps");

			/**
			 * TODO: PASS THROUGH THE FOLOWING PARAMS TO THE FUNCTION
			 * -- lat
			 * -- lng
			 */
			map = new Map($map[0], {
				center: { lat: -34.397, lng: 150.644 },
				zoom: 8,
				mapTypeId: "satellite",
			});
		}

		// TODO: NEED TO GET ENTRIES FROM G FORM AND PLOT THEM
		// TODO: Need to add the ability to pin a comment
		// TODO: Need to load in a KML
		// TODO: need to add in filters

		initMap();

		// FIXME: Map is not returnin because it is being returned before the async function is resolved
		return map;
	}

	/**
	 * Loops through all feedback maps on the page and initialises them via IntersectionObserver or straight away if not supported
	 * @returns {void}
	 */
	function initialiseFeedbackMaps() {
		var feedbackMaps = [].slice.call(
			document.querySelectorAll(".feedback-map")
		);

		// Only proceed if there are maps to initialise
		if (!feedbackMaps.length) {
			return;
		}

		if ("IntersectionObserver" in window) {
			let observeFeedbackMaps = new IntersectionObserver(
				function (entries, observer) {
					entries.forEach(function (entry) {
						if (entry.isIntersecting) {
							var map = initialiseFeedbackMap($(entry.target));
							console.log(map);
							observeFeedbackMaps.unobserve(entry.target);
						}
					});
				},
				// Pre-empt by initializing 300px early
				{ rootMargin: "0px 0px 300px 0px" }
			);

			feedbackMaps.forEach(function (element) {
				observeFeedbackMaps.observe(element);
			});
		} else {
			// For browsers that don't support intersection observer, load all images straight away
			feedbackMaps.forEach(function (element) {
				var map = initialiseFeedbackMap($(element));
			});
		}
	}

	// If we are in the editor, run on render_block_preview
	if (window.acf) {
		window.acf.addAction(
			"render_block_preview",
			function ($elem, blockDetails) {
				// FIXME: THIS IS NOT INITIALISING THE MAP ON THE EDITOR
				console.log($elem, blockDetails);
				if ($elem[0].innerHTML.includes("feedback-map")) {
					initialiseFeedbackMaps();
				}
			}
		);
	} else {
		// Otherwise run on DOMContentLoaded for the front end
		document.addEventListener("DOMContentLoaded", function () {
			initialiseFeedbackMaps();
		});
	}
})(jQuery);

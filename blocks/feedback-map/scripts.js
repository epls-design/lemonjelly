(function ($) {
	// TODO: ADD event listener for esc, if active infowindow close it

	/**
	 * Shows the UI elements on a map
	 * @param {google.maps.Map} map - The map object
	 */
	function showMapInterface(map) {
		map.setOptions({
			mapTypeControl: true,
			mapTypeControlOptions: {
				mapTypeIds: ["roadmap", "satellite"],
				style: google.maps.MapTypeControlStyle.DROPDOWN_MENU,
			},
			zoomControl: true,
			zoomControlOptions: {
				position: google.maps.ControlPosition.RIGHT_TOP,
			},
		});

		return map;
	}

	/**
	 * Generates the markup for a given marker
	 * @param {Object} markerData - The data for the marker
	 * @returns {HTMLElement} - The marker content
	 */
	function generateMarkerContent(markerData) {
		if (!markerData.fields) {
			return "";
		}

		console.log(markerData);

		var content = document.createElement("div");
		var ul = document.createElement("ul");

		content.classList.add("marker-content");
		content.appendChild(ul);

		for (let key in markerData.fields) {
			if (markerData.fields[key].isHidden !== true) {
				var li = document.createElement("li");
				li.innerHTML = `<span class="field-label">${markerData.fields[key].label}: </span><span class="value">${markerData.fields[key].value}</span>`;
				ul.appendChild(li);
			}
		}

		// Append Like button
		if (markerData.allowLikes) {
			var likeButton = document.createElement("button");
			likeButton.classList.add("like-button");
			likeButton.innerHTML = "Like";
			likeButton.addEventListener("click", function () {});
			content.appendChild(likeButton);
		}

		return content;
	}

	/**
	 * Adds a marker to the map
	 * @param {google.maps.Map} map - The map object
	 * @param {Object} markerData - The data for the marker
	 * @returns  {google.maps.Marker} - The marker object
	 */
	function addMarker(map, markerData) {
		var latLng = {
			lat: parseFloat(markerData.lat),
			lng: parseFloat(markerData.lng),
		};

		// TODO: Move to https://developers.google.com/maps/documentation/javascript/reference/advanced-markers#AdvancedMarkerElement
		var marker = new google.maps.Marker({
			position: latLng,
			map: map,
			// TODO: ICON OVERRIDE WOULD BE NICE
		});

		// Show the marker content when clicked
		// TODO: CENTER MAP ON MARKER
		google.maps.event.addListener(marker, "click", function () {
			map.infowindow.setOptions({
				content: generateMarkerContent(markerData),
			});
			map.infowindow.open(map, marker);
		});

		return marker;
	}

	function plotMarkers(map) {
		// Get the jQuery element the map is attached to
		let $mapElement = $(map.getDiv());

		let formId = $mapElement.data("form-id");
		if (!formId) {
			return;
		}

		let formEntries = [];

		// Get form entries using AJAX
		$.ajax({
			type: "GET",
			url: feedbackMapsParams.ajaxUrl,
			data: {
				action: "feedback_map_entries",
				formId: formId,
				nonce: feedbackMapsParams.nonce,
			},
			dataType: "json",
			success: function (response, textStatus, xhr) {
				let formEntries = response.data;
				if (formEntries.length) {
					formEntries.forEach(function (entry) {
						addMarker(map, entry);
					});
				}
			},
			error: function (error) {
				// NO ENTRIES FOUND
				// console.error(error);
			},
		});
	}

	/**
	 * Initialises a single feedback map using the Google Maps Javascript API
	 * @see https://developers.google.com/maps/documentation/javascript/ for documentation
	 *
	 * @param {*} $mapElement - jQuery object of the map element
	 * @returns {google.maps.Map} - The map object
	 */
	async function initialiseFeedbackMap($mapElement) {
		let map;
		let mapZoom = $mapElement.data("zoom") ? $mapElement.data("zoom") : 15;
		let minMapZoom = mapZoom - 3 < 12 ? 12 : mapZoom - 3; // Dont let min Zoom go below 12

		let mapProps = {
			center: {
				lat: $mapElement.data("lat"),
				lng: $mapElement.data("lng"),
			},
			zoom: mapZoom,
			mapTypeId: "satellite",
			scrollwheel: false,
			disableDefaultUI: true,
			minZoom: minMapZoom,
			maxZoom: 20,
			// TODO: Add in restrictions for mapBounds?
		};

		async function initMap() {
			// Destructure the objects required  from the google maps library
			const { Map, KmlLayer } = await google.maps.importLibrary("maps");

			// Create a new map
			map = new Map($mapElement[0], mapProps);

			// If there is KML data load it in
			// @link https://developers.google.com/maps/documentation/javascript/kml
			if ($mapElement.data("overlay-source")) {
				let overlaySource = $mapElement.data("overlay-source");

				// Remove the data attr from the map element
				$mapElement.removeAttr("data-overlay-source");

				// Create a new KML layer
				var kmlLayer = new KmlLayer(overlaySource, {
					suppressInfoWindows: true,
					preserveViewport: true,
					map: map,
				});

				kmlLayer.addListener("status_changed", function () {
					if (kmlLayer.getStatus() !== "OK") {
						// Error handling for KML layer
						console.error("KML Layer failed to load:", kmlLayer.getStatus());
					}
				});
			}
		}

		// TODO: NEED TO GET ENTRIES FROM G FORM AND PLOT THEM
		// TODO: Need to add the ability to pin a comment
		// TODO: need to add in filters

		await initMap();

		// TODO: DISPLAY INTRO TEXT IF FIRST TIME HERE

		// When the map is ready, show the UI elements
		showMapInterface(map);

		plotMarkers(map);

		map.infowindow = new google.maps.InfoWindow();

		// TODO: PLOT MARKERS + CLUSTER THEM
		// TODO: ADD FILTERING TO MARKERS

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

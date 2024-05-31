(function ($) {
	// TODO: ADD event listener for esc, if active infowindow close it
	// TODO Add esc e vent listener, if add Feedback is active, close it (there is already one on the modal, but need to close the button too)
	// TODO: ADD CLUSTERING https://developers.google.com/maps/documentation/javascript/marker-clustering
	// TODO: On Gravity Form submit, close the feedback form and add a marker to the map opening the infowindow ? (or just show a message) - See what client wants in the end

	/********************************************
	 * HELPERS
	 ********************************************/

	function darkenColor(color, percent) {
		// Remove the hash at the start if it's there
		color = color.replace(/^#/, "");

		// Parse the r, g, b values
		let r = parseInt(color.substring(0, 2), 16);
		let g = parseInt(color.substring(2, 4), 16);
		let b = parseInt(color.substring(4, 6), 16);

		// Calculate the darker color values
		r = Math.floor(r * (1 - percent / 100));
		g = Math.floor(g * (1 - percent / 100));
		b = Math.floor(b * (1 - percent / 100));

		// Ensure the values are within the valid range (0-255)
		r = Math.max(0, Math.min(255, r));
		g = Math.max(0, Math.min(255, g));
		b = Math.max(0, Math.min(255, b));

		// Convert back to hex and return the darkened color
		return (
			"#" +
			[r, g, b]
				.map((value) => {
					let hex = value.toString(16);
					return hex.length === 1 ? "0" + hex : hex;
				})
				.join("")
		);
	}

	/********************************************
	 * EVENT LISTENERS - ESC KEYS
	 ********************************************/

	// document.addEventListener("jfModalClosed", function (event) {
	// 	console.log(event.detail);
	// 	// Object { closedModalId: "modal-group-1" }
	// });

	/********************************************
	 * PLOT MARKERS
	 ********************************************/

	/**
	 * Plots markers on the given map by sending an AJAX request to the server to get the form entries
	 * @param {google.maps.Map} map - The map object
	 */
	function plotMarkers(map) {
		// Get the jQuery element the map is attached to
		let $mapElement = $(map.getDiv());

		let formId = $mapElement.data("form-id");
		if (!formId) {
			return;
		}

		let ajaxData = {
			action: "feedback_map_entries",
			formId: formId,
			nonce: feedbackMapsParams.nonce,
		};

		// Get form entries using AJAX
		$.ajax({
			type: "GET",
			url: feedbackMapsParams.ajaxUrl,
			data: ajaxData,
			dataType: "json",
			success: function (response, textStatus, xhr) {
				let formEntries = response.data;
				if (formEntries.length) {
					formEntries.forEach(function (entry) {
						let marker = addMarker(map, entry);

						// Add the marker to the map object
						if (!map.markers) {
							map.markers = [];
						}
						map.markers.push(marker);
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
	 * Adds a marker to the map
	 * @param {google.maps.Map} map - The map object
	 * @param {Object} markerData - The data for the marker
	 * @returns {google.maps.Marker} - The marker object
	 */

	// TODO: Move to https://developers.google.com/maps/documentation/javascript/reference/advanced-markers#AdvancedMarkerElement before deprecated
	function addMarker(map, markerData) {
		let $mapElement = $(map.getDiv());
		let hasFilter = $mapElement.data("filterby");

		var latLng = {
			lat: parseFloat(markerData.lat),
			lng: parseFloat(markerData.lng),
		};

		let markerProps = {
			position: latLng,
			map: map,
		};

		let baseColor = "#ff0000";

		// If the map has a filter, get the relevant field, and add the attributes to the marker
		if (hasFilter) {
			let value = markerData.fields[hasFilter].value;
			if (value !== "") {
				// If value is not an array (eg. a string) make it into an array - this is so markers can have multiple filters
				if (!Array.isArray(value)) {
					value = [value];
				}

				/**
				 * Determine whether or or not the marker should be colored based on the first item in the filter array
				 * If the filter is not found, the marker will be colored red by default
				 */
				let baseColorLookup = value[0] ? value[0] : null;

				if (baseColorLookup) {
					let relevantFilter = $mapElement
						.closest(".feedback-map-wrapper")
						.find(
							".feedback-map-filter input[value='" + baseColorLookup + "']"
						);

					if (relevantFilter.length > 0) {
						var style = getComputedStyle(relevantFilter[0]);
						let accentColor = style.getPropertyValue("--accent-color");

						if (accentColor) baseColor = accentColor;
					}
				}

				markerProps.filter = value;
			}
			$mapElement.removeAttr("data-filterby");
		}

		// Example usage:
		let strokeColor = darkenColor(baseColor, 40);

		const svgMarker = {
			path: "M4.9,2C3.6,0.7,1.9,0,0,0l0,0c-1.9,0-3.6,0.7-4.9,2C-6.3,3.4-7,5-7,7c0,1,0.2,2.1,0.8,3.4c0.5,1.3,1.1,2.4,1.7,3.4 s1.3,2.1,2.1,3.1c0.8,1.1,1.4,1.8,1.7,2.2c0.3,0.4,0.6,0.6,0.8,0.9l0.8-0.8c0.5-0.6,1-1.3,1.7-2.3s1.4-2,2-3.1 			c0.7-1.1,1.3-2.3,1.8-3.5C6.7,9.1,7,8,7,7C7,5,6.3,3.4,4.9,2z M0,10.1c-1.8,0-3.2-1.5-3.2-3.2S-1.8,3.8,0,3.8S3.2,5.2,3.2,7 			S1.8,10.1,0,10.1z",
			fillColor: baseColor,
			fillOpacity: 1,
			strokeWeight: 1.2,
			strokeColor: strokeColor,
			rotation: 0,
			scale: 2,
			anchor: new google.maps.Point(0, 20),
		};

		markerProps.icon = svgMarker;

		var marker = new google.maps.Marker(markerProps);

		// Darken marker on hover
		marker.addListener("mouseover", function () {
			marker.setIcon({
				...svgMarker,
				fillColor: darkenColor(baseColor, 10),
			});
		});

		// Reset marker on mouseout
		marker.addListener("mouseout", function () {
			marker.setIcon(svgMarker);
		});

		// Show the marker content when clicked
		google.maps.event.addListener(marker, "click", function () {
			if (!map.hasActiveModal) {
				map.infowindow.setOptions({
					content: generateMarkerContent(markerData),
				});
				map.infowindow.open(map, marker);

				map.panTo(marker.getPosition());
			}
		});

		return marker;
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

		// Append Like button if likes are enabled
		if (markerData.likes !== undefined) {
			let currentLikes = markerData.likes ? markerData.likes : 0;

			var likeButton = document.createElement("button");
			likeButton.classList.add("like-button", "button", "success", "xsmall");

			likeButton.innerHTML = currentLikes + " likes";

			if (markerData.userHasLiked) {
				likeButton.disabled = true;
				likeButton.title = "You have already liked this";
			}

			likeButton.addEventListener("click", function () {
				if (markerData.userHasLiked) {
					return;
				}
				$.ajax({
					type: "POST",
					url: feedbackMapsParams.ajaxUrl,
					data: {
						action: "like_feedback_entry",
						entryId: markerData["entry_id"],
						nonce: feedbackMapsParams.nonce,
					},
					dataType: "json",
					success: function (response, textStatus, xhr) {
						let respData = response.data;
						likeButton.innerHTML = respData + " likes";
						likeButton.disabled = true;
						markerData.userHasLiked = true;
						markerData.likes = respData;
					},
					error: function (error) {},
				});
			});

			content.appendChild(likeButton);
		}

		return content;
	}

	/********************************************
	 * User Interface
	 ********************************************/

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
			scaleControl: true,
			zoomControl: true,
			zoomControlOptions: {
				position: google.maps.ControlPosition.RIGHT_BOTTOM,
			},
		});

		return map;
	}

	/**
	 * If the map has an intro screen, add event listener to the button to start the map and remove the intro screen
	 *
	 * @param {*} map  - The map object
	 */
	function addIntroScreen(map) {
		let $mapElement = $(map.getDiv());
		let hasIntro = $mapElement.data("has-intro");

		if (!hasIntro) return;

		let introElement = $mapElement
			.closest(".feedback-map-wrapper")
			.find(".feedback-map-intro");
		let button = introElement.find("button").get(0);

		button.addEventListener("click", function () {
			introElement.fadeOut(300);
			$mapElement.addClass("user-interacted");

			setTimeout(() => {
				$mapElement.removeAttr("data-has-intro");
			}, 300);
		});
	}

	/********************************************
	 * Filter markers
	 ********************************************/

	/**
	 * Adds filtering to the map based on the data-filterby attribute
	 * @param {google.maps.Map} map - The map object
	 * @returns {void}
	 */
	function addFiltering(map) {
		let $mapElement = $(map.getDiv());
		let allowsFiltering = $mapElement.data("filterby");

		if (!allowsFiltering) {
			return;
		}

		let filterForm = $mapElement
			.closest(".feedback-map-wrapper")
			.find(".feedback-map-filters");

		let filterInputs = filterForm.find("input[type=checkbox]");
		// Add event listener to filter inputs to show/hide markers when checked
		filterInputs.on("change", function () {
			// hide any open infowindows
			if (map.infowindow) {
				map.infowindow.close();
			}

			// Update active Map Filters to match the checked inputs
			let checkedFilters = filterInputs
				.filter(":checked")
				.map(function () {
					return $(this).val();
				})
				.toArray();
			map.activeFilters = checkedFilters;

			// Determine whether or not to show each marker based on the active filters
			if (map.activeFilters.length > 0) {
				map.markers.forEach(function (marker) {
					if (marker.filter) {
						if (marker.filter) {
							marker.setVisible(
								marker.filter.some((filter) =>
									map.activeFilters.includes(filter)
								)
							);
						} else {
							marker.setVisible(false);
						}
					}
				});
			} else {
				// No filters => show all markers
				map.markers.forEach(function (marker) {
					marker.setVisible(true);
				});
			}
		});
	}

	/********************************************
	 * USER FEEDBACK
	 ********************************************/

	/**
	 * Adds user feedback functionality to the map
	 * @param {google.maps.Map} map - The map object
	 * @returns {void}
	 */
	function addUserFeedback(map) {
		let $mapElement = $(map.getDiv());

		let allowsFeedback = $mapElement.data("feedback-active");

		if (allowsFeedback != true) {
			return;
		}

		$mapElement.removeAttr("data-feedback-active");

		let feedbackButton = $mapElement
			.closest(".feedback-map-wrapper")
			.find(".share-feedback-button");

		let addMarkerControls = $mapElement
			.closest(".feedback-map-wrapper")
			.find(".add-marker-controls");

		if (!feedbackButton.length) {
			return;
		}

		feedbackButton.on("click", function () {
			if (map.infowindow) {
				map.infowindow.close();
			}
			feedbackButton.fadeOut(150);
			feedbackButton.attr("aria-expanded", "true");

			setTimeout(() => {
				addMarkerControls.fadeIn(150);
				addMarkerControls.attr("aria-hidden", "false");
				map.hasActiveModal = true;
			}, 200);
		});

		let feedbackButtonOpener = $mapElement
			.closest(".feedback-map-wrapper")
			.find(".open-feedback-modal");

		feedbackButtonOpener.on("click", function () {
			let latLng = map.getCenter();
			let $form = $mapElement.closest(".feedback-map-wrapper").find("form");
			let $latInput = $form.find(".gfield.latitude input");
			let $lngInput = $form.find(".gfield.longitude input");

			$latInput.val(latLng.lat());
			$lngInput.val(latLng.lng());
		});

		let cancelButton = $mapElement
			.closest(".feedback-map-wrapper")
			.find(".cancel");

		cancelButton.on("click", function () {
			addMarkerControls.fadeOut(300);
			addMarkerControls.attr("aria-hidden", "true");

			setTimeout(() => {
				feedbackButton.fadeIn(50);
				feedbackButton.attr("aria-expanded", "false");
				map.hasActiveModal = false;
			}, 350);
		});
	}

	/********************************************
	 * INITIALISE MAPS
	 ********************************************/

	/**
	 * Initialises a single feedback map using the Google Maps Javascript API
	 * @see https://developers.google.com/maps/documentation/javascript/ for documentation
	 *
	 * @param {*} $mapElement - jQuery object of the map element
	 * @returns {google.maps.Map} - The map object
	 */
	async function initialiseSingleMap($mapElement) {
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
		};

		/**
		 * If the map has a restriction provided, calculate the lat and lng bounds
		 */
		if ($mapElement.data("restriction")) {
			const distance = $mapElement.data("restriction"); // Distance in meters
			const earthRadius = 6371000;

			const latOffset = (distance / earthRadius) * (180 / Math.PI);
			const lngOffset =
				(distance /
					(earthRadius *
						Math.cos(($mapElement.data("lat") * Math.PI) / 180.0))) *
				(180 / Math.PI);

			const latMaxDistance = $mapElement.data("lat") + latOffset;
			const latMinDistance = $mapElement.data("lat") - latOffset;
			const lngMaxDistance = $mapElement.data("lng") + lngOffset;
			const lngMinDistance = $mapElement.data("lng") - lngOffset;

			mapBounds = {
				north: latMaxDistance,
				south: latMinDistance,
				west: lngMinDistance,
				east: lngMaxDistance,
			};

			mapProps.restriction = {
				latLngBounds: mapBounds,
				strictBounds: false,
			};

			$mapElement.removeAttr("data-restriction");
		}

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

		// Wait for the map to be initialised
		await initMap();

		// Populate the map with markers and add UI elements
		showMapInterface(map);
		plotMarkers(map);
		addUserFeedback(map);
		addFiltering(map);
		addIntroScreen(map);

		// Set up an infoWindow and activeFilters property on the map object
		map.infowindow = new google.maps.InfoWindow();
		map.activeFilters = {};

		return map;
	}

	/**
	 * Loops through all feedback maps on the page and initialises them via IntersectionObserver or straight away if not supported
	 * @returns {void}
	 */
	function initialiseAllMaps() {
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
							var map = initialiseSingleMap($(entry.target));
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
				var map = initialiseSingleMap($(element));
			});
		}
	}

	// If we are in the editor, run on render_block_preview
	if (window.acf) {
		window.acf.addAction(
			"render_block_preview/type=ezpz/feedback-map",
			function () {
				initialiseAllMaps();
			}
		);
	} else {
		// Otherwise run on DOMContentLoaded for the front end
		document.addEventListener("DOMContentLoaded", function () {
			initialiseAllMaps();
		});
	}
})(jQuery);

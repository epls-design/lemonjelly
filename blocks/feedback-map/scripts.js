(function ($) {
	// TODO: ADD event listener for esc, if active infowindow close it
	// TODO Add esc e vent listener, if add Feedback is active, close it
	// TODO: Add intro screen
	// TODO: ADD CLUSTERING
	// TODO: On Gravity Form submit, close the feedback form and add a marker to the map opening the infowindow

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
	function addMarker(map, markerData) {
		var latLng = {
			lat: parseFloat(markerData.lat),
			lng: parseFloat(markerData.lng),
		};

		let markerProps = {
			position: latLng,
			map: map,
			// TODO: ICON OVERRIDE WOULD BE NICE
		};

		let $mapElement = $(map.getDiv());

		// If the map has a filter, get the relevant field, and add the attributes to the marker
		let hasFilter = $mapElement.data("filterby");
		if (hasFilter) {
			let value = markerData.fields[hasFilter].value;
			if (value !== "") {
				// TODO: CHECK WHAT HAPPENS IF ARRAY - EG CHECKBOX
				// TODO: IN HERE WE WOULD CHANGE THE COLOR OF THE MARKER TOO BASED ON THE FILTER
				markerProps.filter = value;
			}
		}

		// TODO: Move to https://developers.google.com/maps/documentation/javascript/reference/advanced-markers#AdvancedMarkerElement
		var marker = new google.maps.Marker(markerProps);

		// Show the marker content when clicked
		google.maps.event.addListener(marker, "click", function () {
			if (!map.hasActiveModal) {
				map.infowindow.setOptions({
					content: generateMarkerContent(markerData),
				});
				map.infowindow.open(map, marker);

				map.setCenter(marker.getPosition());
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
			zoomControl: true,
			zoomControlOptions: {
				position: google.maps.ControlPosition.RIGHT_BOTTOM,
			},
		});

		return map;
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
						if (map.activeFilters.includes(marker.filter)) {
							marker.setVisible(true);
						} else {
							marker.setVisible(false);
						}
					} else {
						marker.setVisible(false);
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

		// Wait for the map to be initialised
		await initMap();

		// Populate the map with markers and add UI elements
		showMapInterface(map);
		plotMarkers(map);
		addUserFeedback(map);
		addFiltering(map);

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
			"render_block_preview",
			function ($elem, blockDetails) {
				// FIXME: THIS IS NOT INITIALISING THE MAP ON THE EDITOR
				console.log($elem, blockDetails);
				if ($elem[0].innerHTML.includes("feedback-map")) {
					initialiseAllMaps();
				}
			}
		);
	} else {
		// Otherwise run on DOMContentLoaded for the front end
		document.addEventListener("DOMContentLoaded", function () {
			initialiseAllMaps();
		});
	}
})(jQuery);

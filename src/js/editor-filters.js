"use strict";
// TODO it would be nice if the editor showed the image and not just the front ned
// Import dependencies
const { addFilter } = wp.hooks;
const { createHigherOrderComponent } = wp.compose;
const { Fragment, useEffect, useState } = wp.element;
const { InspectorControls, MediaUpload, MediaUploadCheck } = wp.blockEditor;
const { PanelBody, Button } = wp.components;
const { __ } = wp.i18n;

const { registerBlockType, unregisterBlockVariation } = wp.blocks;

/**
 * Adds imageId attribute to core/list-item block
 * @param {*} settings Block settings
 * @param {*} name Block name
 * @returns Block settings with added attribute
 */
const ezpzListItemImageAttribute = (settings, name) => {
	if (name === "core/list-item") {
		settings.attributes = {
			...settings.attributes,
			imageId: {
				type: "number",
				default: 0,
			},
		};
	}
	return settings;
};

// Apply filters to add the attribute and controls to the block
addFilter(
	"blocks.registerBlockType",
	"ezpz/core/list-item/attributes",
	ezpzListItemImageAttribute
);

/**
 * Adds image field to core/list block
 * @see https://css-tricks.com/a-crash-course-in-wordpress-block-filters/
 * @see https://developer.wordpress.org/block-editor/developers/filters/block-filters/#using-filters
 */
const ezpzAddListElementImage = createHigherOrderComponent((BlockEdit) => {
	return (props) => {
		const {
			attributes: { imageId },
			setAttributes,
			name,
		} = props;

		if (name !== "core/list-item") {
			return <BlockEdit {...props} />;
		}

		// State to hold the image URL
		const [imageUrl, setImageUrl] = useState("");

		// Fetch the image URL whenever imageId changes
		useEffect(() => {
			if (imageId) {
				const attachment = new wp.media.model.Attachment({ id: imageId });
				attachment.fetch().then((data) => {
					console.log(data);
					setImageUrl(data.sizes.thumbnail.url);
				});
			} else {
				setImageUrl(""); // Reset URL if no imageId is set
			}
		}, [imageId]);

		// Set imageId to empty string if undefined
		if (typeof imageId === "undefined") {
			setAttributes({ imageId: "" });
		}

		return (
			<Fragment>
				<BlockEdit {...props} />
				<InspectorControls>
					<PanelBody
						title={__("Bullet Point Image", "lemonjelly")}
						initialOpen={true}
					>
						<p
							style={{
								fontSize: "12px",
								fontStyle: "normal",
								color: "rgb(117, 117, 117)",
							}}
						>
							{__(
								"If you would like to replace the bullet point with an image, please specify the image here. Your image will be cropped and scaled to a square shape on the front end.",
								"lemonjelly"
							)}
						</p>
						<MediaUploadCheck>
							{imageId ? (
								<div>
									<img
										src={imageUrl}
										alt={__("Selected image", "lemonjelly")}
										style={{
											width: "60px",
											marginBottom: "10px",
											maxWidth: "100%",
											display: "block",
										}}
									/>
									<Button
										isSecondary
										onClick={() => setAttributes({ imageId: "" })}
										style={{ marginRight: "5px" }}
									>
										{__("Remove Image", "lemonjelly")}
									</Button>
									<MediaUpload
										onSelect={(media) => setAttributes({ imageId: media.id })}
										allowedTypes={["image"]}
										render={({ open }) => (
											<Button isPrimary onClick={open}>
												{__("Replace Image", "lemonjelly")}
											</Button>
										)}
									/>
								</div>
							) : (
								<MediaUpload
									onSelect={(media) => setAttributes({ imageId: media.id })}
									allowedTypes={["image"]}
									render={({ open }) => (
										<Button isPrimary onClick={open}>
											{__("Upload / Select Image", "lemonjelly")}
										</Button>
									)}
								/>
							)}
						</MediaUploadCheck>
					</PanelBody>
				</InspectorControls>
			</Fragment>
		);
	};
}, "ezpzAddListElementImage");

addFilter(
	"editor.BlockEdit",
	"ezpz/core/list-item/inspector-controls",
	ezpzAddListElementImage
);

/**
 * Filters ezpz/column to add support for 5 column and 6 column layouts
 */
wp.domReady(() => {
	const fiveColumns = wp.element.createElement(
		wp.primitives.SVG,
		{ xmlns: "http://www.w3.org/2000/svg", viewBox: "0 0 48 48" },
		wp.element.createElement(wp.primitives.Path, {
			d: "M39,12H9c-1.1,0-2,0.9-2,2v20c0,1.1,0.9,2,2,2h30c1.1,0,2-0.9,2-2V14C41,12.9,40.1,12,39,12z M9,34V14h1.7h2.7 v20h-2.7H9z M17,34h-1.6V14H17h1h1.8v20H18H17z M23,34h-1.2V14H23h2h1.2v20H25H23z M28.2,34V14H31h1.6v20H31H28.2z M39,34h-4.4V14 H39V34z",
		})
	);

	const sixColumns = wp.element.createElement(
		wp.primitives.SVG,
		{ xmlns: "http://www.w3.org/2000/svg", viewBox: "0 0 48 48" },
		wp.element.createElement(wp.primitives.Path, {
			d: "M39,12H9c-1.1,0-2,0.9-2,2v20c0,1.1,0.9,2,2,2h30c1.1,0,2-0.9,2-2V14C41,12.9,40.1,12,39,12z M9,34V14h1.7h1.6 v20h-1.6H9z M15,34h-0.7V14H15h2h0.7v20H17H15z M19.7,34V14H20h3v20h-3H19.7z M25,14h3.3v20H25V14z M33,34h-2h-0.7V14H31h2h0.7v20 H33z M39,34h-3.3V14H39V34z",
		})
	);

	wp.blocks.registerBlockVariation("ezpz/columns", {
		name: "five-columns-equal",
		title: __("5 columns"),
		description: __("Five columns; equal split"),
		icon: fiveColumns,
		innerBlocks: [
			["ezpz/column", { width: "20%" }],
			["ezpz/column", { width: "20%" }],
			["ezpz/column", { width: "20%" }],
			["ezpz/column", { width: "20%" }],
			["ezpz/column", { width: "20%" }],
		],
		scope: ["block"],
	});

	wp.blocks.registerBlockVariation("ezpz/columns", {
		name: "six-columns-equal",
		title: __("6 columns"),
		description: __("Six columns; equal split"),
		icon: sixColumns,
		innerBlocks: [
			["ezpz/column", { width: "16.66%" }],
			["ezpz/column", { width: "16.66%" }],
			["ezpz/column", { width: "16.66%" }],
			["ezpz/column", { width: "16.66%" }],
			["ezpz/column", { width: "16.66%" }],
			["ezpz/column", { width: "16.66%" }],
		],
		scope: ["block"],
	});
});

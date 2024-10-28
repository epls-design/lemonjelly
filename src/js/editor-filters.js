"use strict";
// TODO it would be nice if the editor showed the image and not just the front ned
// Import dependencies
const { addFilter } = wp.hooks;
const { createHigherOrderComponent } = wp.compose;
const { Fragment, useEffect, useState } = wp.element;
const { InspectorControls, MediaUpload, MediaUploadCheck } = wp.blockEditor;
const { PanelBody, Button } = wp.components;
const { __ } = wp.i18n;

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

import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { useEffect, useRef, useState } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { MapBlock } from './map';
import {
	BaseControl,
	PanelBody,
	TextControl,
	ToggleControl,
	ColorPalette,
} from '@wordpress/components';

export default function Edit({ attributes, setAttributes, clientId }) {
	const {
		preloadData,
		height,
		mapId,
		eventPinColor,
		eventPinBorderColor,
		groupPinColor,
		groupPinBorderColor,
	} = attributes;

	const ref = useRef(null);
	const loadedPreloadData = useRef(false);
	const [data, setData] = useState(null);

	useEffect(() => {
		// only run once (react strict mode)
		if (ref.current) {
			return;
		}

		// load the ref
		const iframe = document.querySelector('iframe[name="editor-canvas"]');
		ref.current = iframe.contentDocument.getElementById(`block-${clientId}`);

		// load google maps
		if (!MapBlock.hasApiKey) {
			apiFetch({
				method: 'GET',
				path: '/nashvilleccr/v1/meta/option?key=google_api_key&type=string',
			}).then((apiKey) => {
				MapBlock.setApiKey(apiKey);
			});
		}
	}, []);

	useEffect(() => {
		if (!preloadData || loadedPreloadData.current) {
			return;
		}

		loadedPreloadData.current = true;

		// load the map data
		apiFetch({
			method: 'GET',
			path: '/nashvilleccr/v1/mapdata',
		}).then((data) => {
			setData(JSON.stringify(data));
		})
	}, [preloadData]);

	useEffect(() => {
		if (preloadData && !data) {
			return;
		}

		// initialize the div
		const map = MapBlock.load(ref.current);

		return () => map.then((m) => m.unload());
	}, [
		preloadData,
		data,
		eventPinColor,
		eventPinBorderColor,
		groupPinColor,
		groupPinBorderColor,
	]);

	const blockProps = {
		"data-map-id": mapId,
		"data-event-pin-color": eventPinColor,
		"data-event-pin-border-color": eventPinBorderColor,
		"data-group-pin-color": groupPinColor,
		"data-group-pin-border-color": groupPinBorderColor,
		"data-preload": preloadData ? data : null,
	};

	return (
		<>
			<InspectorControls>
				<PanelBody title="Settings">
					<BaseControl __nextHasNoMarginBottom>
						<BaseControl.VisualLabel>Preload Data</BaseControl.VisualLabel>
						<ToggleControl
							label="Preload Data"
							checked={preloadData}
							onChange={(preloadData) => setAttributes({ preloadData })}
						/>
					</BaseControl>

					<TextControl
						__nextHasNoMarginBottom
						label="Height"
						value={height}
						onChange={(height) => setAttributes({ height })}
					/>

					<BaseControl __nextHasNoMarginBottom>
						<BaseControl.VisualLabel>Event Pin Color</BaseControl.VisualLabel>
						<ColorPalette
							value={eventPinColor}
							asButtons
							clearable={false}
							onChange={(eventPinColor) => setAttributes ({ eventPinColor })}
						/>
					</BaseControl>

					<BaseControl __nextHasNoMarginBottom>
						<BaseControl.VisualLabel>Event Pin Color</BaseControl.VisualLabel>
						<ColorPalette
							label="Event Pin Border Color"
							value={eventPinBorderColor}
							clearable={false}
							onChange={(eventPinBorderColor) => setAttributes ({ eventPinBorderColor })}
						/>
					</BaseControl>

					<BaseControl __nextHasNoMarginBottom>
						<BaseControl.VisualLabel>Event Pin Color</BaseControl.VisualLabel>
						<ColorPalette
							label="Group Pin Color"
							value={groupPinColor}
							clearable={false}
							onChange={(groupPinColor) => setAttributes ({ groupPinColor })}
						/>
					</BaseControl>

					<BaseControl __nextHasNoMarginBottom>
						<BaseControl.VisualLabel>Event Pin Color</BaseControl.VisualLabel>
						<ColorPalette
							label="Group Pin Border Color"
							value={groupPinBorderColor}
							clearable={false}
							onChange={(groupPinBorderColor) => setAttributes ({ groupPinBorderColor })}
						/>
					</BaseControl>
				</PanelBody>
			</InspectorControls>
			<div
				/* ref not passed during render for some reason, so we load it manually above */
				{...useBlockProps(blockProps)}
			>
				<div style={{ "pointer-events": "none" }}>
					<div style={{ height }}></div>
				</div>
			</div>
		</>
	);
}

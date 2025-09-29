import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { useEffect, useRef, useState } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { MapBlock } from './map';
import { PanelBody, TextControl, ToggleControl, ColorPicker } from '@wordpress/components';

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
					<ToggleControl
						label="Preload Data"
						checked={preloadData}
						onChange={(preloadData) => setAttributes({ preloadData })}
					/>
					<TextControl
						label="Height"
						value={height}
						onChange={(height) => setAttributes({ height })}
					/>
					<ColorPicker
						label="Event Pin Color"
						color={eventPinColor}
						enableAlpha
						onChange={(eventPinColor) => setAttributes ({ eventPinColor })}
					/>
					<ColorPicker
						label="Event Pin Border Color"
						color={eventPinBorderColor}
						enableAlpha
						onChange={(eventPinBorderColor) => setAttributes ({ eventPinBorderColor })}
					/>
					<ColorPicker
						label="Group Pin Color"
						color={groupPinColor}
						enableAlpha
						onChange={(groupPinColor) => setAttributes ({ groupPinColor })}
					/>
					<ColorPicker
						label="Group Pin Border Color"
						color={groupPinBorderColor}
						enableAlpha
						onChange={(groupPinBorderColor) => setAttributes ({ groupPinBorderColor })}
					/>
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

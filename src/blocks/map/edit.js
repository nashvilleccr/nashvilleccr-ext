import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { useEffect, useRef, useState } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import {
	BaseControl,
	PanelBody,
	TextControl,
	ToggleControl,
	ColorPalette,
	Icon,
} from '@wordpress/components';
import { dragHandle } from '@wordpress/icons';

export default function Edit({ attributes, setAttributes }) {
	const {
		preload,
		height,
		mapId,
		eventPinColor,
		eventPinBorderColor,
		groupPinColor,
		groupPinBorderColor,
	} = attributes;

	/** @type React.MutableRefObject<NccrMapElement> */
	const ref = useRef(null);
	const [data, setData] = useState(null);

	useEffect(() => {
		// load the map data
		apiFetch({
			method: 'GET',
			path: '/nashvilleccr/v1/mapdata',
		}).then((data) => {
			setData(JSON.stringify(data));
		})
	}, []);

	const dragProps = {
		style: {
			background: "var(--wp-components-color-accent, var(--wp-admin-theme-color, #3858e9))",
			color: "var(--wp-components-color-accent-inverted, #fff)",
			fill: "var(--wp-components-color-accent-inverted, #fff)",
			width: "100%",
			height: "28px",
			display: "flex",
			alignItems: "center",
			padding: "0 2px",
			cursor: "move",
			borderRadius: "5px 5px 0 0",
		}
	}

	return (
		<>
			<InspectorControls>
				<PanelBody title="Settings">
					<BaseControl __nextHasNoMarginBottom>
						<BaseControl.VisualLabel>Preload Data</BaseControl.VisualLabel>
						<ToggleControl
							label="Preload Data"
							checked={preload}
							onChange={(preload) => setAttributes({ preload })}
						/>
					</BaseControl>

					<TextControl
						__nextHasNoMarginBottom
						label="Map ID"
						value={mapId}
						onChange={(mapId) => setAttributes({ mapId })}
					/>

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
						<BaseControl.VisualLabel>Event Pin Border Color</BaseControl.VisualLabel>
						<ColorPalette
							value={eventPinBorderColor}
							clearable={false}
							onChange={(eventPinBorderColor) => setAttributes ({ eventPinBorderColor })}
						/>
					</BaseControl>

					<BaseControl __nextHasNoMarginBottom>
						<BaseControl.VisualLabel>Group Pin Color</BaseControl.VisualLabel>
						<ColorPalette
							value={groupPinColor}
							clearable={false}
							onChange={(groupPinColor) => setAttributes ({ groupPinColor })}
						/>
					</BaseControl>

					<BaseControl __nextHasNoMarginBottom>
						<BaseControl.VisualLabel>Group Pin Pin Color</BaseControl.VisualLabel>
						<ColorPalette
							value={groupPinBorderColor}
							clearable={false}
							onChange={(groupPinBorderColor) => setAttributes ({ groupPinBorderColor })}
						/>
					</BaseControl>
				</PanelBody>
			</InspectorControls>
			<div {...useBlockProps()}>
				<div {...dragProps}>
					<Icon icon={dragHandle} />
				</div>
				<div draggable="true">
					<nccr-map
						ref={ref}
						map-id={mapId}
						height={height}
						event-pin-color={eventPinColor}
						event-pin-border-color={eventPinBorderColor}
						group-pin-color={groupPinColor}
						group-pin-border-color={groupPinBorderColor}
						preload={preload ? data : null}
					></nccr-map>
				</div>
			</div>
		</>
	);
}

// inject scripts into canvas iframe
window.addEventListener('load', () => {
	/** @type {HTMLIFrameElement} */
	const canvas = document.querySelector('iframe[name="editor-canvas"]');

	const parent = {
		map: document.getElementById('wp-importmap'),
		view: document.getElementById('nashvilleccr-map-view-script-module-js-module'),
		data: document.getElementById('wp-script-module-data-nashvilleccr-map-view-script-module'),
	};

	const map = canvas.contentDocument.createElement('script');
	map.type = parent.map.type;
	map.id = parent.map.id;
	map.innerText = parent.map.innerText;
	canvas.contentDocument.head.appendChild(map);

	const view = canvas.contentDocument.createElement('script');
	view.type = parent.view.type;
	view.src = parent.view.src;
	view.id = parent.view.id;
	canvas.contentDocument.head.appendChild(view);

	const data = canvas.contentDocument.createElement('script');
	data.type = parent.data.type;
	data.id = parent.data.id;
	data.innerText = parent.data.innerText;
	canvas.contentDocument.head.appendChild(data);
});

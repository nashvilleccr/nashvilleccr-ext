import { useBlockProps } from '@wordpress/block-editor';
import { useEffect, useRef } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { loadAPI, loadMap } from './map';

export default function Edit({ clientId }) {
	const ref = useRef(null);

	useEffect(() => {
		// only run once (react strict mode)
		if (ref.current) {
			return;
		}

		// load the ref
		const iframe = document.querySelector('iframe[name="editor-canvas"]');
		ref.current = iframe.contentDocument.getElementById(`block-${clientId}`);

		// load google maps
		apiFetch({
			method: 'GET',
			path: '/nashvilleccr/v1/meta/option?key=google_api_key&type=string',
		}).then((apiKey) => {
			loadAPI(apiKey);
		});

		// initialize the div
		loadMap(ref.current);
	}, [])

	return (
		// ref not passed during render for some reason, so we load it manually above
		<div /* ref={ref} */ {...useBlockProps()}></div>
	);
}

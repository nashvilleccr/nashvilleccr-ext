import { useBlockProps } from '@wordpress/block-editor';
import { useEffect, useRef } from '@wordpress/element';
import { loadMap } from './map';

export default function Edit({ clientId }) {
	const ref = useRef(null);

	useEffect(() => {
		const iframe = document.querySelector('iframe[name="editor-canvas"]');
		const iframeDoc = iframe.contentDocument;
		const div = iframeDoc.getElementById(`block-${clientId}`);

		loadMap(div).then((Map) => {
			ref.current = Map;
		});

		return () => { ref.current = null };
	}, []);

	return (
		<div {...useBlockProps()}></div>
	);
}

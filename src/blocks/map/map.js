import { Loader } from '@googlemaps/js-api-loader';

const loader = new Loader({
  apiKey: globalThis.GOOGLE_API_KEY,
  version: "weekly",
  libraries: []
});

let $loader = loader.importLibrary("maps");

/**
 * @param {HTMLElement} div - element for the map to be loaded into
 */
export const loadMap = async (div) => {
    const { Map } = await $loader;

    /** @type google.map.MapsLibrary */
    const map = new Map(div, {
        center: {
            lat: 35.88,
            lng: -86.38,
        },
        zoom: 7,
    });

    return map;
}

export const loadMaps = () => {
    for (const div of document.querySelectorAll('.wp-block-nashvilleccr-map')) {
        loadMap(div);
    }
};
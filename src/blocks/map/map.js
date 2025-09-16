import { Loader } from '@googlemaps/js-api-loader';

export let loadAPI;

/** @type Promise<string> */
const $apiKey = new Promise((resolve) => {
    loadAPI = resolve;

    if (globalThis.GOOGLE_API_KEY) {
        loadAPI(globalThis.GOOGLE_API_KEY);
    }
});

const $loader = $apiKey.then((apiKey) => new Loader({
    apiKey,
    version: "weekly",
    libraries: [],
}));

/** @type Promise<google.maps.MapsLibrary> */
export const $maps = $loader.then((loader) => loader.importLibrary("maps"))

if (globalThis.GOOGLE_API_KEY) {
    loadAPI(globalThis.GOOGLE_API_KEY);
}

/**
 * @param {HTMLElement} div - element for the map to be loaded into
 */
export const loadMap = async (div) => {
    const { Map } = await $maps;

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

/**
 * @returns {Promise<google.map.MapsLibrary[]>}
 */
export const loadMaps = async () => {
    const divs = document.querySelectorAll('.wp-block-nashvilleccr-map');
    return Promise.all([...divs].map((div) => loadMap(div)));
};
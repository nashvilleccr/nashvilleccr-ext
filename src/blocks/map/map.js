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
    libraries: ["maps"],
}));

/** @type Promise<google.maps.MapsLibrary> */
export const $maps = $loader.then((loader) => loader.importLibrary("maps"))

const TENNESSEE_CENTER = {
    lat: 35.88,
    lng: -86.38,
};

const TENNESSEE_BOUNDS = {
    north: 38,
    east: -82,
    south: 34,
    west: -90.5,
};

/** @param {HTMLElement} div - element for the map to be loaded into */
export const loadMap = async (div) => {
    const { Map } = await $maps;

    /** @type google.map.MapsLibrary */
    const map = new Map(div, {
        center: TENNESSEE_CENTER,
        restriction: {
            latLngBounds: TENNESSEE_BOUNDS,
            strictBounds: true,
        },
        zoom: 7,
        mapTypeControl: false,
        streetViewControl: false,
    });

    return map;
}

/** @returns {Promise<google.map.MapsLibrary[]>} */
export const loadMaps = async () => {
    const divs = document.querySelectorAll('.wp-block-nashvilleccr-map');
    return Promise.all([...divs].map((div) => loadMap(div)));
};
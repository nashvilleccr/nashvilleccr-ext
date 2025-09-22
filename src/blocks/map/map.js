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

/** @type Promise<google.maps.CoreLibrary> */
export const $core = $loader.then((loader) => loader.importLibrary("core"));

/** @type Promise<google.maps.MapsLibrary> */
export const $maps = $loader.then((loader) => loader.importLibrary("maps"))

const TENNESSEE_CENTER = {
    lat: 35.88,
    lng: -86.38,
};

const TENNESSEE_BOUNDS = {
    "south": 34.07981003231745,
    "west": -90.39000976562498,
    "north": 37.64018931325273,
    "east": -82.36999023437498
};

/** @param {HTMLElement} div - element for the map to be loaded into */
export const loadMap = async (div) => {
    const { event } = await $core;
    const { Map } = await $maps;

    /** @type google.map.MapsLibrary */
    const map = new Map(div, {
        center: TENNESSEE_CENTER,
        restriction: {
            latLngBounds: TENNESSEE_BOUNDS,
            strictBounds: false,
        },
        zoom: 7,
        mapTypeControl: false,
        streetViewControl: false,
    });

    const updateBounds = () => {
        map.fitBounds(TENNESSEE_BOUNDS);
    };

    const observer = new ResizeObserver(updateBounds);

    event.addListenerOnce(map, 'tilesloaded', () => {
        observer.observe(div);
    });

    return map;
}

/** @returns {Promise<google.map.MapsLibrary[]>} */
export const loadMaps = async () => {
    const divs = document.querySelectorAll('.wp-block-nashvilleccr-map');
    return Promise.all([...divs].map((div) => loadMap(div)));
};
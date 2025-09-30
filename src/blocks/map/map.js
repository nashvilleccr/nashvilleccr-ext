import { Loader } from '@googlemaps/js-api-loader';

/**
 * @typedef {{
 *   core: google.maps.CoreLibrary,
 *   maps: google.maps.MapsLibrary,
 *   marker: google.maps.MarkerLibrary,
 * }} libs
 */

/**
 * @typedef {{
 *   title: string,
 *   link: string,
 *   location: number,
 *   contacts: number[],
 *   $location: locationData,
 *   $contacts: contactData[],     
 * }} eventData
 */

/**
 * @typedef {{
 *   title: string,
 *   link: string,
 *   location: number,
 *   contacts: number[],
 *   $location: locationData,
 *   $contacts: contactData[],     
 * }} groupData
 */

/**
 * @typedef {{
 *   title: string,
 *   link: string,
 *   lat: number,
 *   lng: number,
 * }} locationData
 */

/**
 * @typedef {{
 *   title: string,
 *   link: string,
 * }} contactData
 */

/**
 * @typedef {{
 *   events: { [id: number]: eventData },
 *   groups: { [id: number]: groupData },
 *   contacts: { [id: number]: contactData },
 *   locations: { [id: number]: locationData },
 * }} data
 */

/**
 * @typedef {{
 *   events: { [id: number]: google.maps.AdvancedMarkerElement },
 *   groups: { [id: number]: google.maps.AdvancedMarkerElement },
 * }} markers
 */

export class MapBlock {
    static hasApiKey = false;

    /** @type (apiKey: string | PromiseLike<string>) => void */
    static setApiKey;

    /** @type Promise<string> */
    static #apiKey = new Promise((resolve) => {
        this.setApiKey = (apiKey) => {
            this.hasApiKey = true;
            resolve(apiKey);
        };

        if (globalThis.GOOGLE_API_KEY) {
            this.setApiKey(globalThis.GOOGLE_API_KEY);
        }
    })

    static #loader = this.#apiKey.then((apiKey) => new Loader({
        apiKey,
        version: "weekly",
        libraries: ["maps", "marker"],
    }));

    static #core = this.#loader.then((loader) => loader.importLibrary("core"));
    static #maps = this.#loader.then((loader) => loader.importLibrary("maps"));
    static #marker = this.#loader.then((loader) => loader.importLibrary("marker"));

    static #TENNESSEE_CENTER = {
        lat: 35.88,
        lng: -86.38,
    };

    static #TENNESSEE_BOUNDS = {
        "south": 34.07981003231745,
        "west": -90.39000976562498,
        "north": 37.64018931325273,
        "east": -82.36999023437498
    };

    static #TENNESSEE_ZOOM = 6.678071905112187;

    /** @type ($block: HTMLDivElement) => Promise<MapBlock> */
    static async load($block) {
        const [core, maps, marker] = await Promise.all([
            this.#core,
            this.#maps,
            this.#marker
        ]);

        return new MapBlock($block, { core, maps, marker });
    }

    /** @type (query?: string) => Promise<MapBlock[]> */
    static async loadAll(query = ".wp-block-nashvilleccr-map") {
        const $blocks = document.querySelectorAll(query);
        return Promise.all([...$blocks].map(($block) => this.load($block)));
    }

    /** @type HTMLDivElement */ $block;
    /** @type HTMLDivElement */ $wrap;
    /** @type HTMLDivElement */ $map;
    /** @type libs */ libs;
    /** @type google.maps.MapsEventListener[] */ listeners;
    /** @type data */ data;
    /** @type string */ mapId;
    /** @type string */ eventPinColor;
    /** @type string */ eventPinBorderColor;
    /** @type string */ groupPinColor;
    /** @type string */ groupPinBorderColor;
    /** @type google.maps.Map */ map;
    /** @type ResizeObserver */ resizeObserver;
    /** @type google.maps.InfoWindow */ infoWindow;
    /** @type markers */ markers;

    /**
     * @type (
     *   div: HTMLDivElement,
     *   mapId: string,
     *   libs: libs,
     * ) */
    constructor($block, libs) {
        this.$block = $block;
        this.$wrap = this.$block.querySelector(':scope > .map-wrapper');
        this.$map = this.$wrap.querySelector(':scope > .map');
        this.libs = libs;
        this.listeners = [];

        this.data = this.#loadDataRefs(JSON.parse($block.getAttribute("data-preload")));
        this.mapId = $block.getAttribute("data-map-id");
        this.eventPinColor = $block.getAttribute("data-event-pin-color");
        this.eventPinBorderColor = $block.getAttribute("data-event-pin-border-color");
        this.groupPinColor = $block.getAttribute("data-group-pin-color");
        this.groupPinBorderColor = $block.getAttribute("data-group-pin-border-color");

        this.map = new this.libs.maps.Map(this.$map, {
            mapId: this.mapId,
            center: MapBlock.#TENNESSEE_CENTER,
            zoom: MapBlock.#TENNESSEE_ZOOM,
            mapTypeControl: false,
            gestureHandling: 'cooperative',
            renderingType: this.libs.maps.RenderingType.VECTOR,
        });

        this.#updateBounds();
        this.resizeObserver = new ResizeObserver(this.#updateBounds);
        this.onLoad(() => {
            this.resizeObserver.observe(this.$block);
        });

        this.infoWindow = new this.libs.maps.InfoWindow();

        this.markers = { events: {}, groups: {} };
        this.#addMarkers(this.data.events, this.markers.events, {
            borderColor: this.eventPinBorderColor,
            background: this.eventPinColor,
            glyphColor: this.eventPinBorderColor,
        })
        this.#addMarkers(this.data.groups, this.markers.groups, {
            borderColor: this.groupPinBorderColor,
            background: this.groupPinColor,
            glyphColor: this.groupPinBorderColor,
        })
    }

    /** @type (fn: () => any) => void */
    onLoad(fn) {
        this.listeners.push(
            this.libs.core.event.addListenerOnce(this.map, "tilesloaded", fn())
        );
    }

    #updateBounds = () => {
        this.map.fitBounds(MapBlock.#TENNESSEE_BOUNDS);
    };

    /** @type (data: data) => data */
    #loadDataRefs(data) {
        if (data === null) {
            return { events: {}, groups: {}, contacts: {}, locations: {} };
        }

        for (const group of Object.values(data.groups)) {
            group.$location = data.locations[group.location]
            group.$contacts = group.contacts.map(id => data.contacts[id]);
        }

        for (const event of Object.values(data.events)) {
            event.$location = data.locations[event.location]
            event.$contacts = event.contacts.map(id => data.contacts[id]);
        }

        return data;
    }

    /**
     * @type (
     *   from: { [id: number]: eventData | groupData },
     *   to: { [id: number]: google.maps.AdvancedMarkerElement },
     *   pinOpts: google.maps.marker.PinElementOptions,
     * ) => void */
    #addMarkers(from, to, pinOpts) {
        for (const [id, data] of Object.entries(from)) {
            const { title, $location } = data;
            const { lat, lng } = $location;

            const pin = new this.libs.marker.PinElement(pinOpts);

            const marker = new this.libs.marker.AdvancedMarkerElement({
                map: this.map,
                title,
                position: { lat, lng },
                content: pin.element,
                gmpClickable: true,
            });

            this.listeners.push(marker.addListener("click", () => {
                this.infoWindow.close();
                this.infoWindow.setContent(title);
                this.infoWindow.open(this.map, marker);
            }));

            to[id] = marker;
        }
    }

    unload() {
        this.resizeObserver.disconnect();
        this.listeners.forEach((l) => l.remove());
        this.$map.textContent = "";
    }
}
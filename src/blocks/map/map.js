import { Loader } from '@googlemaps/js-api-loader';
import block from "./block.json";
import { getIpInfo } from '#nccr/ip-api';

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
 *   events: {
 *     [id: number]: {
 *       pin: google.maps.marker.PinElement,
 *       marker: google.maps.marker.AdvancedMarkerElement,
 *     }
 *   },
 *   groups: {
 *     [id: number]: {
 *       pin: google.maps.marker.PinElement,
 *       marker: google.maps.marker.AdvancedMarkerElement,
 *     }
 *   },
 * }} markers
 */

export class NccrMapElement extends HTMLElement {
    static loaded = false;

    /** @type (apiKey: string | PromiseLike<string>) => void */
    static load;

    /** @type Promise<string> */
    static #apiKey = new Promise((resolve) => {
        this.load = (apiKey) => {
            globalThis.getIpInfo = getIpInfo;
            this.loaded = true;
            if (!customElements.get('nccr-map')) {
                customElements.define('nccr-map', this);
            }
            resolve(apiKey);
        };
    });

    static #loader = this.#apiKey.then((apiKey) => new Loader({
        apiKey,
        version: "weekly",
        libraries: ["maps", "marker"],
    }));

    /** @type Promise<libs> */
    static #libs = Promise.all([
        this.#loader.then((loader) => loader.importLibrary("core")),
        this.#loader.then((loader) => loader.importLibrary("maps")),
        this.#loader.then((loader) => loader.importLibrary("marker")),
    ]).then(([core, maps, marker]) => ({ core, maps, marker }));

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

    static #TENNESSEE_ZOOM = 6;

    static observedAttributes = [
        "preload",
        "height",
        "map-id",
        "event-pin-color",
        "event-pin-border-color",
        "group-pin-color",
        "group-pin-border-color",
    ];

    /** @type boolean */ loaded = false;
    /** @type markers */ markers = { events: {}, groups: {} };

    /** @type (reason?: any) => void */ #cancelLoad;
    /** @type Promise<libs> */ $libs;
    /** @type libs */ libs;
    /** @type google.maps.MapElement */ $map;
    /** @type google.maps.Map */ map;
    /** @type data */ data;
    /** @type google.maps.InfoWindow */ infoWindow;
    /** @type ResizeObserver */ resizeObserver;
    /** @type string */ height;
    /** @type string */ mapId;
    /** @type string */ eventPinColor;
    /** @type string */ eventPinBorderColor;
    /** @type string */ groupPinColor;
    /** @type string */ groupPinBorderColor;

    constructor() {
        super();
    }

    async connectedCallback() {
        const $libs = new Promise((resolve, cancel) => {
            this.#cancelLoad = cancel;
            resolve(NccrMapElement.#libs);
        });

        this.libs = await $libs;
        this.loaded = true;

        if (!this.$map) {
            this.#loadMap();
        }
    }

    disconnectedCallback() {
        this.#cancelLoad();
        this.loaded = false;
    }

    attributeChangedCallback(name, oldValue, newValue) {
        if (!this.loaded || oldValue === newValue) {
            return;
        }

        switch (name) {
            case "map-id":
                this.$map.remove();
                this.resizeObserver.disconnect();
                this.#loadMap();
                break;
            case "preload":
                for (const { marker } of this.#markerIter('events')) {
                    marker.map = null;
                }
                for (const { marker } of this.#markerIter('groups')) {
                    marker.map = null;
                }
                this.markers = { events: {}, groups: {} };
                this.#loadMarkers();
                break;
            case "height":
                this.$map.style.height = newValue;
                this.libs.core.event.trigger(this.map, 'resize');
                break;
            case "event-pin-color":
                for (const { pin } of this.#markerIter('events')) {
                    this.eventPinColor = newValue;
                    pin.background = this.eventPinColor;
                }
                break;
            case "event-pin-border-color":
                for (const { pin } of this.#markerIter('events')) {
                    this.eventPinBorderColor = newValue;
                    pin.borderColor = this.eventPinBorderColor;
                    pin.glyphColor = this.eventPinBorderColor;
                }
                break;
            case "group-pin-color":
                for (const { pin } of this.#markerIter('groups')) {
                    this.groupPinColor = newValue;
                    pin.background = this.groupPinColor;
                }
                break;
            case "group-pin-border-color":
                for (const { pin } of this.#markerIter('groups')) {
                    this.groupPinBorderColor = newValue;
                    pin.borderColor = this.groupPinBorderColor;
                    pin.glyphColor = this.groupPinBorderColor;
                }
                break;
        }
    }

    #loadMap() {
        this.mapId = this.getAttribute("map-id") ?? block.attributes.mapId.default;
        this.height = this.getAttribute("height") ?? block.attributes.height.default;

        this.$map = document.createElement("gmp-map");
        this.$map.style.width = "100%";
        this.$map.style.height = this.height;

        this.map = this.$map.innerMap;
        this.map.setOptions({
            mapId: this.mapId,
            center: NccrMapElement.#TENNESSEE_CENTER,
            zoom: NccrMapElement.#TENNESSEE_ZOOM,
            mapTypeControl: false,
            gestureHandling: 'cooperative',
            renderingType: this.libs.maps.RenderingType.VECTOR,
        });

        this.infoWindow = new this.libs.maps.InfoWindow();
        this.resizeObserver = new ResizeObserver(this.#updateBounds);

        this.libs.core.event.addListenerOnce(this.map, "tilesloaded", () => {
            this.resizeObserver.observe(this.$map);
            setTimeout(() => this.#loadMarkers());
        });

        this.appendChild(this.$map);
    }

    #loadMarkers() {
        this.data = this.#getDataWithRefs();
        this.eventPinColor = this.getAttribute('event-pin-color')
            ?? block.attributes.eventPinColor.default;
        this.eventPinBorderColor = this.getAttribute('event-pin-border-color')
            ?? block.attributes.eventPinBorderColor.default;
        this.groupPinColor = this.getAttribute('group-pin-color')
            ?? block.attributes.groupPinColor.default;
        this.groupPinBorderColor = this.getAttribute('group-pin-border-color')
            ?? block.attributes.groupPinBorderColor.default;

        const pinOptsMap = {
            events:  {
                borderColor: this.eventPinBorderColor,
                background: this.eventPinColor,
                glyphColor: this.eventPinBorderColor,
            },
            groups:  {
                borderColor: this.groupPinBorderColor,
                background: this.groupPinColor,
                glyphColor: this.groupPinBorderColor,
            },
        }

        for (const [key, pinOpts] of Object.entries(pinOptsMap)) {
            for (const [id, data] of Object.entries(this.data[key])) {
                /** @type eventData | groupData */
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

                this.$map.appendChild(marker);

                marker.addListener("click", () => {
                    this.infoWindow.close();
                    this.infoWindow.setContent(title);
                    this.infoWindow.open(this.map, marker);
                });

                this.markers[key][id] = { pin, marker };
            }
        }
    }

    #updateBounds = () => {
        this.map.fitBounds(NccrMapElement.#TENNESSEE_BOUNDS);
    };

    /** @type () => data */
    #getDataWithRefs() {
        const data = JSON.parse(this.getAttribute("preload"));

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
     *   src: "events" | "groups"
     * ) => Generator<{
     *   id: string,
     *   marker: google.maps.marker.AdvancedMarkerElement,
     *   pin: google.maps.marker.PinElement,
     * }>
     */
    *#markerIter(src) {
        for (const [id, obj] of Object.entries(this.markers[src])) {
            const { pin, marker } = obj;
            yield { id, marker, pin };
        }
    }
}
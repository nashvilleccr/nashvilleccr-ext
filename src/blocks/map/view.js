import { NccrMapElement } from './map';

const $data = document.getElementById('wp-script-module-data-nashvilleccr-map-view-script-module');
const { googleApiKey } = JSON.parse($data.textContent);
NccrMapElement.load(googleApiKey);
import { createSessionPromise } from "#nccr/storage-promise";
import { sleep } from "#nccr/util";

/** @type {{ apiFetch: import("@wordpress/api-fetch").default }} */
const { apiFetch } = top.wp;

/**
 * @typedef {{
 *   query: string,
 *   status: "success",
 *   country: string,
 *   countryCode: string,
 *   region: string,
 *   regionName: string,
 *   city: string,
 *   zip: string,
 *   lat: number,
 *   lon: number,
 *   timezone: string,
 *   isp: string,
 *   org: string,
 *   as: string,
 * } | {
 *   query: string,
 *   status: "fail",
 *   message: string,
 * }} ipInfo
 */

/** @type Promise<ipInfo> */
let ipInfo;

/** @type {() => Promise<ipInfo>} */
export const getIpInfo = () => {
    return ipInfo ?? (ipInfo = createSessionPromise("nccrIpInfo", async (state) => {
        do {
            const exponent = state.get("exponent") ?? 0;
            const resume = state.get("resume");

            if (resume !== null) {
                await sleep(resume - Date.now());
                state.delete("resume");
            }

            try {
                return await apiFetch({
                    path: "/nashvilleccr/v1/ipinfo"
                });
            } catch (e) {
                console.warn(e);
            }

            const baseDelay = Math.pow(2, exponent) * 1000;
            const baseJitter = Math.pow(2, exponent - 1) * 1000;
            const jitter = Math.round(baseJitter * (Math.random() - 0.5));
            const delay = baseDelay + jitter;

            state.set("resume", Date.now() + delay);
            state.set("exponent", exponent + 1);
        } while (true);
    }));
};
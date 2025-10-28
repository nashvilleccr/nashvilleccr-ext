/**
 * @typedef {{
 *   translations: {
 *     [slug: string]: string,
 *   },
 *   current: string,
 * }} langData
 */

export const checkSwitchLanguage = () => {
    const $data = document.getElementById('wp-script-module-data-#nccr/language-switcher');
    /** @type {langData} */
    const data = JSON.parse($data.textContent);
    const requested = new Set(navigator.languages.map((lang) => lang.split('-')[0]));

    for (const lang of requested) {
        if (lang === data.current) {
            return; // already correct
        }

        const url = data.translations[lang];

        if (url) {
            return askSwitchLanguage(data.current, lang, url); // ask to switch
        }
    }

    return; // no supported language
};

const askSwitchLanguage = (from, to, url) => {
    console.log({ event: "modal", from, to, url });
}

checkSwitchLanguage();
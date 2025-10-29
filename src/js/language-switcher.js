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
            return askSwitchLanguage(data, lang); // ask to switch
        }
    }

    return; // no supported language
};

const askSwitchLanguage = (data, lang) => {
    const to = data.translations[lang];

    const from_lang = to.strings[`%${data.current}%`];
    const to_lang = to.strings[`%${lang}%`];

    const title = to.strings['%title%']
        .replaceAll('%to%', to_lang);

    const message = to.strings['%message%']
        .replaceAll('%from%', `<strong>${from_lang}</strong>`)
        .replaceAll('%to%', `<strong>${to_lang}</strong>`);

    const persist = to.strings['%persist%'];
    const yes = to.strings['%yes%'];
    const no = to.strings['%no%'];

    console.log({
        title,
        message,
        persist,
        yes,
        no,
    });
}

checkSwitchLanguage();
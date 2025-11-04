import { store, getElement } from '@wordpress/interactivity';
import "../css/language-switcher.scss";

const { state, actions } = store("#nccr/language-switcher", {
    state: {
        disable: false,
        strings: {},
        ref: null,
        data: {},
        lang: "",
        get browserLang() {
            return state.data.translations[state.lang];
        },
    },
    actions: {
        checkSwitchLanguage: () => {
            const requested = new Set(navigator.languages.map((lang) => lang.split('-')[0]));
            const $data = document.getElementById('wp-script-module-data-#nccr/language-switcher');
            state.data = JSON.parse($data.textContent);
            state.ref = getElement().ref;

            for (const lang of requested) {
                if (lang === state.data.current) {
                    return; // already correct
                }

                const url = state.data.translations[lang];

                if (url) {
                    state.lang = lang;
                    return actions.trySwitchLanguage(); // try to switch
                }
            }

            return; // no supported language
        },
        trySwitchLanguage: () => {
            const disable = window.localStorage.getItem("nccrLanguageSwitcher_disable");

            if (disable) {
                return;
            }

            actions.askSwitchLanguage();
        },
        askSwitchLanguage: () => {
            const strings = state.browserLang.strings;
            const from_lang = strings[`%${state.data.current}%`];
            const to_lang = strings[`%${state.lang}%`];

            state.strings.title = strings['%title%']
                .replaceAll('%to%', to_lang);

            state.strings.message = strings['%message%']
                .replaceAll('%from%', from_lang)
                .replaceAll('%to%', to_lang);

            state.strings.disable = strings['%disable%'];
            state.strings.yes = strings['%yes%'];
            state.strings.no = strings['%no%'];

            state.ref.showModal();
        },
        toggleDisable() {
            state.disable = !state.disable;
        },
        yes() {
            if (state.disable) {
                window.localStorage.setItem("nccrLanguageSwitcher_disable", "true");
            }

            window.location.href = state.browserLang.url;
        },
        no() {
            if (state.disable) {
                window.localStorage.setItem("nccrLanguageSwitcher_disable", "true");
            }

            state.ref.close();
        },
    }
});
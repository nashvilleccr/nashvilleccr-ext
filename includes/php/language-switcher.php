<?php namespace NashvilleCCR; defined('ABSPATH') || exit;

class LanguageSwitcher {
    const KEYS = [
        '%title%',
        '%message%',
        '%disable%',
        '%yes%',
        '%no%',
    ];

    const DEFAULTS = [
        'en' => [
            '%en%' => 'English',
            '%es%' => 'Spanish',
            '%title%' => 'Switch to %to%?',
            '%message%' => 'The current page is written in %from%, but your browser\'s requested language is %to%. Would you like to switch to %to%?',
            '%disable%' => 'Don\'t ask again',
            '%yes%' => 'Yes',
            '%no%' => 'No',
        ],
        'es' => [
            '%en%' => 'inglés',
            '%es%' => 'español',
            '%title%' => '¿Cambiar al %to%?',
            '%message%' => 'La página actual está escrita en %from%, pero el idioma solicitado por su navegador es el %to%. ¿Desea cambiar al %to%?',
            '%disable%' => 'No vuelvas a preguntar',
            '%yes%' => 'Sí',
            '%no%' => 'No',
        ],
    ];

    static function load() {
        add_action('init', [self::class, 'default_translations']);
        add_action('admin_init', [self::class, 'add_translations']);
        add_action('wp_enqueue_scripts', [self::class, 'enqueue_scripts']);
    }

    static function default_translations() {
        foreach (self::DEFAULTS as $slug => $defaults) {
            $term = get_term_by('slug', $slug, 'language');

            if (!$term) {
                continue; // do nothing if 'en' or 'es' not set
            }

            $res = get_term_meta($term->term_id, '_pll_strings_translations', true) ?? [];
            $keyed = [];
            $updated = false;

            foreach ($res as $pair) {
                $keyed[$pair[0]] = $pair[1];
            }

            foreach ($defaults as $name => $translation) {
                if (!empty($keyed[$name])) {
                    continue;
                }

                $updated = true;
                $res[] = [$name, $translation];
            }

            if ($updated) {
                update_term_meta($term->term_id, '_pll_strings_translations', $res);
            }
        }
    }

    static function add_translations() {
        pll_register_string('english', '%en%', 'plugins/nashvilleccr-ext');
        pll_register_string('spanish', '%es%', 'plugins/nashvilleccr-ext');
        pll_register_string('language-switcher/title', '%title%', 'plugins/nashvilleccr-ext');
        pll_register_string('language-switcher/message', '%message%', 'plugins/nashvilleccr-ext', true);
        pll_register_string('language-switcher/disable', '%disable%', 'plugins/nashvilleccr-ext');
        pll_register_string('yes', '%yes%', 'plugins/nashvilleccr-ext');
        pll_register_string('no', '%no%', 'plugins/nashvilleccr-ext');
    }

    static function enqueue_scripts() {
        $langs = 0;

        foreach (pll_the_languages(['raw' => true]) as $slug => $lang) {
            if (!$lang['no_translation']) {
                $langs++;
            }
        }

        if ($langs < 2) {
            return; // don't add scripts if no alternative languages
        }

        wp_enqueue_script_module('#nccr/language-switcher');

        add_filter(
            'script_module_data_#nccr/language-switcher',
            [self::class, 'add_language_data']
        );

        if (Meta::option('language_switcher_override_defaults')) {
            wp_deregister_style('#nccr/language-switcher');
            $styles = Meta::option('language_switcher_css', FieldType::String);
            wp_register_style('#nccr/language-switcher', false);
            wp_enqueue_style('#nccr/language-switcher');
            wp_add_inline_style('#nccr/language-switcher', $styles);
        } else {
            wp_enqueue_style('#nccr/language-switcher');
        }


        add_action('wp_footer', [self::class, 'footer']);
    }

    static function footer() {
        if (Meta::option('language_switcher_override_defaults')) {
            echo Meta::option('language_switcher_html', FieldType::String);
        } else {
            self::footer_default();
        }
    }

    static function footer_default() { ?>
        <dialog
            class="nccr-language-switcher"
            data-wp-interactive="#nccr/language-switcher"
            data-wp-init="actions.checkSwitchLanguage"
            closedby="any">
            <form method="dialog">
                <h2 data-wp-text="state.strings.title"></h2>
                <p data-wp-text="state.strings.message"></p>
                <div class="footer">
                    <label class="disable">
                        <input
                            type="checkbox"
                            name="disable"
                            value="1"
                            data-wp-bind--checked="state.disable"
                            data-wp-on--click="actions.toggleDisable"
                        >
                        <span data-wp-text="state.strings.disable"></span>
                    </label>
                    <div class="buttons">
                        <button
                            type="reset"
                            data-wp-text="state.strings.no"
                            data-wp-on--click="actions.no"
                        ></button>
                        <button
                            type="submit"
                            data-wp-text="state.strings.yes"
                            data-wp-on--click="actions.yes"
                        ></button>
                    </div>
                </div>
            </form>
        </dialog>
    <?php }

    static function add_language_data(array $data): array {
        $data['translations'] = [];

        foreach (pll_the_languages(['raw' => true]) as $slug => $lang) {
            if ($lang['current_lang']) {
                $data['current'] = $slug;
            }

            if ($lang['no_translation']) {
                continue;
            }

            $strings = [];

            foreach (self::KEYS as $key) {
                $translated = pll_translate_string($key, $slug);

                if ($translated === $key) {
                    $translated = self::DEFAULTS['en'][$key]; // default to English
                }

                $strings[$key] = $translated;
            }

            $data['translations'][$slug] = [
                'url' => $lang['url'],
                'defaultName' => $lang['name'],
                'strings' => $strings,
            ];
        }

        foreach ($data['translations'] as $outer => &$outer_lang) {
            foreach ($data['translations'] as $inner => &$inner_lang) {
                $key = "%{$inner}%";
                $translated = pll_translate_string($key, $outer);

                if ($translated === $key) {
                    $translated = $inner_lang['defaultName'];
                }

                $outer_lang['strings'][$key] = $translated;
            }
        }

        foreach ($data['translations'] as $slug => &$lang) {
            unset($lang['defaultName']);
        }

        return $data;
    }
}

return LanguageSwitcher::class;
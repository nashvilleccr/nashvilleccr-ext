<?php namespace NashvilleCCR; defined('ABSPATH') || exit;

class LanguageSwitcher {
    const DEFAULTS = [
        'en' => [
            '%en%' => 'English',
            '%es%' => 'Spanish',
            '%title%' => 'Switch to %to%?',
            '%message%' => 'The current page is written in %from%, but your browser\'s requested language is %to%. Would you like to switch to %to%?',
            '%persist%' => 'Don\'t ask again',
            '%yes%' => 'Yes',
            '%no%' => 'No',
        ],
        'es' => [
            '%en%' => 'inglés',
            '%es%' => 'español',
            '%message%' => 'La página actual está escrita en %from%, pero el idioma solicitado por su navegador es el %to%. ¿Desea cambiar al %to%?',
            '%persist%' => 'No vuelvas a preguntar',
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
        pll_register_string('language-switcher/persist', '%persist%', 'plugins/nashvilleccr-ext');
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
    }

    static function add_language_data(array $data): array {
        $data['translations'] = [];

        foreach (pll_the_languages(['raw' => true]) as $slug => $lang) {
            if ($lang['current_lang']) {
                $data['current'] = $slug;
            }

            if ($lang['no_translation']) {
                continue;
            }

            $data['translations'][$slug] = $lang['url'];
        }

        return $data;
    }
}

return LanguageSwitcher::class;
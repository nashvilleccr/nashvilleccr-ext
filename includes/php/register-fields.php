<?php namespace NashvilleCCR; defined('ABSPATH') || exit;

class RegisterFields {
    const JSON_DIR = Plugin::DIR . "/includes/acf-json";

    static function load() {
        add_filter('acf/settings/load_json', [self::class, 'acf__settings__load_json']);

        if (defined('DEV')) {
            add_filter('acf/json/save_paths', [self::class, 'acf__json__save_paths'], 10, 2);
            add_filter('acf/prepare_field_group_for_import', [self::class, 'prepare_json_for_import'], 20, 1);
            add_filter('acf/prepare_field_group_for_export', [self::class, 'prepare_json_for_export'], 20, 1);
            add_filter('acf/prepare_post_type_for_import', [self::class, 'prepare_json_for_import'], 20, 1);
            add_filter('acf/prepare_post_type_for_export', [self::class, 'prepare_json_for_export'], 20, 1);
            add_filter('acf/prepare_taxonomy_for_import', [self::class, 'prepare_json_for_import'], 20, 1);
            add_filter('acf/prepare_taxonomy_for_export', [self::class, 'prepare_json_for_export'], 20, 1);
            add_filter('acf/prepare_ui_options_page_for_import', [self::class, 'prepare_json_for_import'], 20, 1);
            add_filter('acf/prepare_ui_options_page_for_export', [self::class, 'prepare_json_for_export'], 20, 1);
        }
    }

    static function acf__json__save_paths($paths, $post) {
        $paths[] = self::JSON_DIR;
        return $paths;
    }

    static function acf__settings__load_json($paths) {
        $paths[] = self::JSON_DIR;
        return $paths;
    }

    static function prepare_json_for_import($group) {
        $group['private'] = false;
        return $group;
    }

    static function prepare_json_for_export($group) {
        $group['private'] = true;
        return $group;
    }
}
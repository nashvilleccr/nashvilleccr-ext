<?php namespace NashvilleCCR; defined('ABSPATH') || exit;

class GoogleMapsSCF {
    static function load() {
        add_action('acf/init', [self::class, 'register_google_api_key']);
    }

    static function register_google_api_key() {
        $api_key = Meta::option('google_api_key', FieldType::String);
        acf_update_setting('google_api_key', $api_key);
    }
}

return GoogleMapsSCF::class;
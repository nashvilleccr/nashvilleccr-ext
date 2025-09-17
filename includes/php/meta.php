<?php namespace NashvilleCCR; defined('ABSPATH') || exit;

enum FieldType {
    case Bool;
    case String;
}

class Meta {
    static $options = [];

    static function init() {
        add_action('rest_api_init', [self::class, 'rest_api_init']);
    }

    static function option($key, FieldType $type = FieldType::Bool) {
        if (isset(self::$options[$key])) {
            return self::$options[$key];
        }

        $res = get_field($key, 'option');

        switch($type) {
            case FieldType::Bool:
                if (!is_bool($res)) {
                    $res = false;
                }
                break;
            case FieldType::String:
                if (!is_string(($res))) {
                    $res = "";
                }
                break;
        }

        return self::$options[$key] = $res;
    }

    static function to_field_type(string $str) {
        switch(strtolower($str)) {
            case "bool":
                return FieldType::Bool;
            case "string":
                return FieldType::String;
            default:
                return null;
        }
    }

    static function rest_api_init() {
        register_rest_route('nashvilleccr/v1', '/meta/option', [
            'methods' => 'GET',
            'callback' => [self::class, 'get_option'],
            'permission_callback' => [self::class, 'option_permissions'],
        ]);
    }

    static function get_option(\WP_REST_Request $request) {
        $key = $request->get_param("key");

        if (is_null($key)) {
            return new \WP_Error(
                'missing_key',
                "Must include a 'key' value",
                ['status' => 400]
            );
        }

        $type = $request->get_param("type") ?? "bool";
        $field_type = self::to_field_type($type);

        if (is_null($field_type)) {
            return new \WP_Error(
                'invalid_type',
                "Invalid 'type' value: {$type}",
                ['status' => 400]
            );
        }

        return new \WP_REST_Response(
            Meta::option($key, $field_type),
            200
        );
    }

    static function option_permissions(\WP_REST_Request $request) {
        if (!is_user_logged_in()) {
            return new \WP_Error(
                'rest_forbidden',
                'Must be logged in to use this endpoint',
                ['status' => 401]
            );
        }

        return true;
    }
}
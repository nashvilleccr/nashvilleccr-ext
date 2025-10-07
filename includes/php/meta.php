<?php namespace NashvilleCCR; defined('ABSPATH') || exit;

enum FieldType {
    case Bool;
    case String;
    case Int;
}

class Meta {
    static $options = [];

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
            case FieldType::Int:
                if (!is_int(($res))) {
                    $res = 0;
                }
                break;
        }

        return self::$options[$key] = $res;
    }

    static function set_option($key, $value) {
        self::$options[$key] = $value;
        update_field($key, $value, 'option');
    }
}
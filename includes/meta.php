<?php namespace NashvilleCCR; defined('ABSPATH') || exit;

enum FieldType {
    case Bool;
    case String;
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
        }

        return self::$options[$key] = $res;
    }
}
<?php namespace NashvilleCCR; defined('ABSPATH') || exit;

enum FieldType {
    case Bool;
    case String;
    case Int;
}

interface IMeta {
    function _option(string $key, FieldType $type);
    function _set_option(string $key, $value);
}

class Meta implements IMeta {
    static $options = [];
    static IMeta $instance;

    static function load() {
        self::$instance = new Meta();
    }

    static function option($key, FieldType $type = FieldType::Bool) {
        return self::$instance->_option($key, $type);
    }

    static function set_option($key, $value) {
        return self::$instance->_set_option($key, $value);
    }

    function _option(string $key, FieldType $type) {
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

    function _set_option($key, $value) {
        self::$options[$key] = $value;
        update_field($key, $value, 'option');
    }
}

return Meta::class;
<?php namespace NashvilleCCR; defined('ABSPATH') || exit;

enum FieldType {
    case Bool;
}

class Meta {
    static $options = [];

    static function option($key, FieldType $type = FieldType::Bool) {
        if (isset(self::$options[$key])) {
            return self::$options[$key];
        }

        // TODO: add checking for get_field to fix coercion issues
        $res = get_field($key, 'option');

        switch($type) {
            case FieldType::Bool:
                if (!is_bool($res)) {
                    self::report_mismatch($key, $res, 'boolean');
                    $res = false;
                }
                break;
        }

        return self::$options[$key] = $res;
    }

    private static function report_mismatch($key, $res, $expected) {
        if (is_null($res)) {
            warn("Missing option field value '{$key}'");
            return;
        }

        $val = var_export($res, true);
        warn("Expected a '{$expected}' from option field '{$key}' but received '{$val}'");
    }
}
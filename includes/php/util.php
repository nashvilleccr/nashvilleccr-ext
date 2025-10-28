<?php namespace NashvilleCCR; defined('ABSPATH') || exit;

class Util {
    static function load() {
        // stub
    }

    static function get_private($instance, string $variable) {
        return \Closure::bind(function() use ($variable) {
            return $this->$variable;
        }, $instance, get_class($instance))();
    }

    static function wp_script_module_is_registered(string $id) {
        $script_modules = wp_script_modules();
        $registered = self::get_private($script_modules, 'registered');

        return isset($registered[$id]);
    }

    static function wp_script_module_is_enqueued(string $id) {
        $script_modules = wp_script_modules();
        $enqueued_before_registered = self::get_private($script_modules, 'enqueued_before_registered');

        if (isset($enqueued_before_registered[$id])) {
            return true;
        }

        $registered = self::get_private($script_modules, 'registered');
        $script = $registered[$id] ?? [];

        return $script['enqueue'] ?? false;
    }
}

return Util::class;
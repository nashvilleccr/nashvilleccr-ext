<?php namespace NashvilleCCR; defined('ABSPATH') || exit;

class RegisterModules {
    static function load () {
        add_action('init', [self::class, 'init']);
    }

    static function init() {
        foreach (glob(Plugin::DIR . '/build/modules/*.js') as $module_file) {
            preg_match('/build\/modules\/(.*)\.js$/', $module_file, $matches);

            $asset_file = preg_replace('/\.js$/', '.asset.php', $module_file);
            $asset = require($asset_file);
            $module_name = '#nccr/' . $matches[1];
            $module_url = plugins_url($matches[0], Plugin::FILE);

            wp_register_script_module(
                $module_name,
                $module_url,
                $asset['dependencies'],
                $asset['version'],
            );
        }
    }
}

return RegisterModules::class;
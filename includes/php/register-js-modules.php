<?php namespace NashvilleCCR; defined('ABSPATH') || exit;

class RegisterJsModules {
    static function load () {
        add_action('init', [self::class, 'init']);
        add_action('wp_enqueue_scripts', [self::class, 'inject_non_module_deps'], 20);
    }

    static function init() {
        foreach (glob(Plugin::DIR . '/build/js/*.js') as $module_file) {
            preg_match('/build\/js\/(.*)\.js$/', $module_file, $matches);

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

            $css_file = preg_replace('/\.js$/', '.css', $module_file);

            if (file_exists($css_file)) {
                $css_url = plugins_url("build/js/{$matches[1]}.css", Plugin::FILE);
                $css_version = filemtime($css_file);

                wp_register_style($module_name, $css_url, [], $css_version);
                wp_style_add_data($module_name, 'path', $css_file);
            }
        }
    }

    static function inject_non_module_deps() {
        $file = Plugin::DIR . '/includes/js-non-module-script-deps.json';
        $json = json_decode(file_get_contents($file));

        foreach ($json as $id => $deps) {
            if (!Util::wp_script_module_is_enqueued($id)) {
                continue;
            }

            foreach ($deps as $dep) {
                wp_enqueue_script($dep);
            }
        }
    }
}

return RegisterJsModules::class;
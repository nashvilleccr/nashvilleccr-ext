<?php
/**
 * Plugin Name:       Nashville CCR Extensions
 * Description:       Plugin to extend the site
 * Version:           0.1.0
 * Requires at least: 6.8
 * Requires PHP:      8.2
 * Author:            Michael LaCorte
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       nccr
 */

namespace NashvilleCCR; defined('ABSPATH') || exit;

class Plugin {
	const DIR = __DIR__;

	static function load() {
		foreach (glob(self::DIR . "/includes/php/*.php") as $file) {
			require $file;
		}

		if (!function_exists('get_field')) {
			warn("Secure Custom Fields plugin required for this plugin to work.");
			return;
		}

		RegisterFields::load();
		RegisterBlocks::load();
		Performance::load();
		Meta::load();
	}
}

function notice($msg) {
	trigger_error($msg, E_USER_NOTICE);
}

function warn($msg) {
	trigger_error($msg, E_USER_WARNING);
}

function debug($title, $data = true) {
	if (!defined('DEV')) {
		return;
	}

	add_action('kadence_before_wrapper', function() use($title, $data) {
		echo "<pre><code>{$title} => " . json_encode($data) . '</code></pre>';
	});
}

add_action('plugins_loaded', [Plugin::class, 'load'], 20);
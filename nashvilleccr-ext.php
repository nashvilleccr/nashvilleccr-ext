<?php
/**
 * Plugin Name:       Nashville CCR Extensions
 * Description:       Plugin to extend the site
 * Version:           0.1.0
 * Requires at least: 6.8
 * Requires PHP:      8.2
 * Author:            Nashville CCR
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       nccr
 * Domain Path:       /languages
 */

namespace NashvilleCCR; defined('ABSPATH') || exit;

class Plugin {
	const DIR = __DIR__;
	const FILE = __FILE__;

	static function load() {
		$cancel = false;

		if (!function_exists('get_field')) {
			add_action('admin_notices', [self::class, 'scf_admin_notice']);
			$cancel = true;
		}

		if (!function_exists('pll_current_language')) {
			add_action('admin_notices', [self::class, 'polylang_admin_notice']);
			$cancel = true;
		}

		if ($cancel) {
			return;
		}

		foreach (glob(self::DIR . "/includes/php/*.php") as $file) {
			$class = require $file;
			add_action('plugins_loaded', [$class, 'load'], 15);
		}
	}

	static function scf_admin_notice() { ?>
		<div class="notice notice-error is-dismissible">
			<p>
				<strong>Nashville CCR Ext</strong> plugin requires either
				<a target="_blank" href="https://wordpress.org/plugins/secure-custom-fields/">Secure Custom Fields</a>
				or
				<a target="_blank" href="https://www.advancedcustomfields.com/pro/">Advanced Custom Fields Pro</a>
				to be active in order to function.
			</p>
		</div>
	<? }

	static function polylang_admin_notice() { ?>
		<div class="notice notice-error is-dismissible">
			<p>
				<strong>Nashville CCR Ext</strong> plugin requires
				<a target="_blank" href="https://wordpress.org/plugins/polylang/">Polylang</a>
				to be active in order to function.
			</p>
		</div>
	<? }
}

add_action('plugins_loaded', [Plugin::class, 'load'], 5);
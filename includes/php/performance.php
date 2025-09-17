<?php namespace NashvilleCCR; defined('ABSPATH') || exit;

class Performance {
    static function load() {
        add_action('init', [self::class, 'init']);
    }

    static function init() {
        if (Meta::option("disable_emoji_support")) {
            remove_action('wp_head', 'print_emoji_detection_script', 7);
            remove_action('admin_print_scripts', 'print_emoji_detection_script');
            remove_action('wp_print_styles', 'print_emoji_styles');
            remove_action('admin_print_styles', 'print_emoji_styles');
            remove_filter('the_content_feed', 'wp_staticize_emoji');
            remove_filter('comment_text_rss', 'wp_staticize_emoji');
            remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
            add_filter('tiny_mce_plugins', [self::class, 'disable_emoji_support_tinymce']);
            add_filter('wp_resource_hints', [self::class, 'disable_emoji_support_dns'], 10, 2);
        }

        if (Meta::option("load_separate_core_block_assets")) {
            add_filter('should_load_separate_core_block_assets', '__return_true');
        }

        if (Meta::option("remove_block_css_globals")) {
            add_action('wp_enqueue_scripts', [self::class, 'remove_block_css_globals'], 20);
        }

        if (Meta::option("disable_classic_theme_css")) {
            add_action('wp_enqueue_scripts', [self::class, 'disable_classic_theme_css'], 20);
        }
    }

    static function disable_emoji_support_tinymce($plugins) {
        return is_array($plugins) ? array_diff($plugins, ['wpemoji']) : [];
    }

    static function disable_emoji_support_dns($urls, $relation_type) {
        if ($relation_type !== 'dns-prefetch') {
            return $urls;
        }

        $emoji_svg_url = apply_filters('emoji_svg_url', 'https://s.w.org/images/core/emoji/2/svg/');

        return array_diff($urls, [$emoji_svg_url]);
    }

    static function remove_block_css_globals() {
        wp_dequeue_style('wp-block-library');
    }

    static function disable_classic_theme_css() {
        wp_dequeue_style('classic-theme-styles');
    }
}
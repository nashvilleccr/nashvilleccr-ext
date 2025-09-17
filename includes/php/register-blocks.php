<?php namespace NashvilleCCR; defined('ABSPATH') || exit;

class RegisterBlocks {
    static $render_blocks = [];

    static function load() {
        add_action('init', [self::class, 'init']);
    }

    static function init() {
        $blocks_dir = Plugin::DIR . '/build/blocks';
        $blocks_manifest = Plugin::DIR . '/build/blocks-manifest.php';

        wp_register_block_metadata_collection($blocks_dir, $blocks_manifest);

        $blocks = \WP_Block_Metadata_Registry::get_collection_block_metadata_files($blocks_dir);

        foreach ($blocks as $block) {
            $meta = register_block_type_from_metadata($block);
            $init = str_replace('block.json', 'init.php', $block);
            $render_block = str_replace('block.json', 'render-block.php', $block);

            if (file_exists(($init))) {
                require_once $init;
            }

            if (file_exists($render_block)) {
                self::$render_blocks[$meta->name] = $render_block;
            }
        }

        add_action('render_block', [self::class, 'render_block'], 10, 2);
    }

    static function render_block($block_content, $block) {
        $file = self::$render_blocks[$block['blockName']] ?? null;

        if (!is_null($file)) {
            require_once $file;
        }

        return $block_content;
    }
}
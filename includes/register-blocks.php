<?php namespace NashvilleCCR; defined('ABSPATH') || exit;

class RegisterBlocks {
    static function init() {
        wp_register_block_metadata_collection(Plugin::$blocks_dir, Plugin::$blocks_manifest);

        $blocks = \WP_Block_Metadata_Registry::get_collection_block_metadata_files(Plugin::$blocks_dir);

        foreach ($blocks as $block) {
            $meta = register_block_type_from_metadata($block);
            $render_block = str_replace('block.json', 'render-block.php', $block);

            if (file_exists($render_block)) {
                Plugin::$render_blocks[$meta->name] = $render_block;
            }
        }

        add_action('render_block', [self::class, 'render_block'], 10, 2);
    }

    static function render_block($block_content, $block) {
        $file = Plugin::$render_blocks[$block['blockName']] ?? null;

        if (!is_null($file)) {
            require_once $file;
        }

        return $block_content;
    }
}
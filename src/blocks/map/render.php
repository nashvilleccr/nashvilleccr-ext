<?php namespace NashvilleCCR; defined('ABSPATH') || exit;

add_action('wp_footer', [MapBlock::class, 'load_api_key']);

?><div <?= get_block_wrapper_attributes([
    'data-preload' => json_encode(
        MapBlock::preload_data()
    ),
]) ?>></div>
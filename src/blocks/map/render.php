<?php namespace NashvilleCCR; defined('ABSPATH') || exit;

?><div <?= get_block_wrapper_attributes([
    'data-preload' => json_encode(
        MapAPI::preload_data()
    ),
]) ?>></div>
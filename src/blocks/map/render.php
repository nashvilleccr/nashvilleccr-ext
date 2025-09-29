<?php namespace NashvilleCCR; defined('ABSPATH') || exit;

add_action('wp_footer', [MapBlock::class, 'load_api_key']);

$attrs = [
    'data-map-id' => $attributes['mapId'],
    'data-event-pin-color' => $attributes['eventPinColor'],
    'data-event-pin-border-color' => $attributes['eventPinBorderColor'],
    'data-group-pin-color' => $attributes['groupPinColor'],
    'data-group-pin-border-color' => $attributes['groupPinBorderColor'],
];

if ($attributes['preloadData']) {
    $attrs['data-preload'] = json_encode(MapBlock::preload_data());
}

?>
<div <?= get_block_wrapper_attributes($attrs) ?>>
    <div>
        <div style="height: <?= $attributes['height'] ?>;"></div>
    </div>
</div>
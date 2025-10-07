<?php namespace NashvilleCCR; defined('ABSPATH') || exit;

?><div <?= get_block_wrapper_attributes() ?>>
    <nccr-map
        map-id="<?= $attributes['mapId'] ?>"
        height="<?= $attributes['height'] ?>"
        event-pin-color="<?= $attributes['eventPinColor'] ?>"
        event-pin-border-color="<?= $attributes['eventPinBorderColor'] ?>"
        group-pin-color="<?= $attributes['groupPinColor'] ?>"
        group-pin-border-color="<?= $attributes['groupPinBorderColor'] ?>"
        <?php if ($attributes['preload']): ?>
        preload="<?= esc_attr(json_encode(MapBlock::preload_data())) ?>"
        <?php endif; ?>
    ></nccr-map>
</div>
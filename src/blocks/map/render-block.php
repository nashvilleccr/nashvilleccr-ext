<?php namespace NashvilleCCR; defined('ABSPATH') || exit;

add_action('wp_footer', function() { ?>
<script>
    globalThis.GOOGLE_API_KEY = "<?= Meta::option("google_api_key", FieldType::String) ?>";
</script>
<?php });
<?php namespace NashvilleCCR; defined('ABSPATH') || exit;

class SymlinkTranslations {
    static function load() {
        $locale = get_locale();

        $to = Plugin::DIR . "/languages/nccr-{$locale}.mo";

        if (file_exists($to)) {
            return;
        }

        $code = substr($locale, 0, 2);
        $from_abs = Plugin::DIR . "/languages/nccr-{$code}.mo";
        $from_rel = './' . basename($from_abs);

        if (!file_exists($from_abs)) {
            return;
        }

        symlink($from_rel, $to);
    }
}

return SymlinkTranslations::class;
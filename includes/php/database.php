<?php namespace NashvilleCCR; defined('ABSPATH') || exit;

class Database {
    const MIGRATION = 1;

    static function load() {
        add_action('init', [self::class, 'init']);
    }

    static function init() {
        if (Meta::option('nccr_migration', FieldType::Int) < self::MIGRATION) {
            self::run_migrations();
        }
    }

    static function run_migrations() {
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        global $wpdb;
        $prefix = "{$wpdb->prefix}nccr";
        $charset = $wpdb->get_charset_collate();

        // location --- state
        dbDelta("CREATE TABLE {$prefix}_location (
            location_id bigint NOT NULL,
            state tinytext NOT NULL,
            PRIMARY KEY  location_id (location_id),
            UNIQUE KEY state (state, location_id)
        ) ENGINE=InnoDB {$charset};");

        // event --- location
        dbDelta("CREATE TABLE {$prefix}_event (
            event_id bigint NOT NULL,
            location_id bigint NOT NULL,
            PRIMARY KEY  event_id (event_id),
            UNIQUE KEY event_by_location (location_id, event_id)
        ) ENGINE=InnoDB {$charset};");

        // event ---< start/end
        dbDelta("CREATE TABLE {$prefix}_event_times (
            event_id bigint NOT NULL,
            start datetime NOT NULL,
            end datetime NOT NULL,
            PRIMARY KEY  event_time (event_id, start, end),
            UNIQUE KEY event_by_start (start, event_id),
            UNIQUE KEY event_by_end (end, event_id)
        ) ENGINE=InnoDB {$charset};");

        // event ---< contact
        dbDelta("CREATE TABLE {$prefix}_event_contacts (
            event_id bigint NOT NULL,
            contact_id bigint NOT NULL,
            PRIMARY KEY  event_contact (event_id, contact_id),
            UNIQUE KEY event_by_contact (contact_id, event_id)
        ) ENGINE=InnoDB {$charset};");

        // group --- location
        dbDelta("CREATE TABLE {$prefix}_group (
            group_id bigint NOT NULL,
            location_id bigint NOT NULL,
            PRIMARY KEY  group_id (group_id),
            UNIQUE KEY group_by_location (location_id, group_id)
        ) ENGINE=InnoDB {$charset};");

        // group ---< contact
        dbDelta("CREATE TABLE {$prefix}_group_contacts (
            group_id bigint NOT NULL,
            contact_id bigint NOT NULL,
            PRIMARY KEY  group_contact (group_id, contact_id),
            UNIQUE KEY group_by_contact (contact_id, group_id)
        ) ENGINE=InnoDB {$charset};");

        Meta::set_option('nccr_migration', self::MIGRATION);
    }
}

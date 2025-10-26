<?php namespace NashvilleCCR; defined('ABSPATH') || exit;

class MapBlock {
    static function load() {
        add_action('enqueue_block_assets', [self::class, 'update_scripts']);
        add_action('rest_api_init', [self::class, 'rest_api_init']);
        add_filter('block_editor_rest_api_preload_paths', [self::class, 'preload_mapdata'], 10, 2);
    }

    static function update_scripts() {
        add_filter(
            'script_module_data_nashvilleccr-map-view-script-module',
            function (array $data): array {
                $data['googleApiKey'] = Meta::option("google_api_key", FieldType::String);
                return $data;
            }
        );
    }

    static function preload_mapdata($preload_paths, $post) {
        $preload_paths[] = "/nashvilleccr/v1/mapdata";
        return $preload_paths;
    }

    const SCHEMA = [
        '$schema' => 'http://json-schema.org/draft-04/schema#',
        'title' => 'Map data',
        'description' => 'Returns event and group data for loading into maps.',
        'type' => 'object',
        'properties' => [
            'events' => [
                'type' => 'object',
                'patternProperties' => [
                    '^[0-9]+$' => self::EVENT_SCHEMA,
                ],
                'additionalProperties' => false,
            ],
            'groups' => [
                'type' => 'object',
                'patternProperties' => [
                    '^[0-9]+$' => self::GROUP_SCHEMA,
                ],
                'additionalProperties' => false,
            ],
            'locations' => [
                'type' => 'object',
                'patternProperties' => [
                    '^[0-9]+$' => self::LOCATION_SCHEMA,
                ],
                'additionalProperties' => false,
            ],
            'contacts' => [
                'type' => 'object',
                'patternProperties' => [
                    '^[0-9]+$' => self::CONTACT_SCHEMA,
                ],
                'additionalProperties' => false,
            ],
        ],
        'additionalProperties' => false,
        'required' => ['events', 'groups'],
    ];

    const EVENT_SCHEMA = [
        'type' => 'object',
        'properties' => [
            'title' => ['type' => 'string'],
            'link' => ['type' => 'string'],
            'location' => ['type' => 'number'],
            'contacts' => [
                'type' => 'array',
                'items' => ['type' => 'number'],
            ],
        ],
        'required' => ['title', 'link', 'location', 'contacts'],
        'additionalProperties' => false,
    ];

    const GROUP_SCHEMA = [
        'type' => 'object',
        'properties' => [
            'title' => ['type' => 'string'],
            'link' => ['type' => 'string'],
            'location' => ['type' => 'number'],
            'contacts' => [
                'type' => 'array',
                'items' => ['type' => 'number'],
            ],
        ],
        'required' => ['title', 'link', 'location', 'contacts'],
        'additionalProperties' => false,
    ];

    const LOCATION_SCHEMA = [
        'type' => 'object',
        'properties' => [
            'title' => ['type' => 'string'],
            'link' => ['type' => 'string'],
            'lat' => ['type' => 'number'],
            'lng' => ['type' => 'number'],
        ],
        'required' => ['title', 'link', 'lat', 'lng'],
        'additionalProperties' => false,
    ];

    const CONTACT_SCHEMA = [
        'type' => 'object',
        'properties' => [
            'title' => ['type' => 'string'],
            'link' => ['type' => 'string'],
        ],
        'required' => ['title', 'link'],
        'additionalProperties' => false,
    ];

    const ARGS_SCHEMA = [
        'type' => [
            'type' => 'string',
            'description' => 'Only return events or groups if param is provided. (Both types if not provided.)',
            'enum' => ['events', 'groups']
        ],
        'state' => [
            'type' => 'string',
            'description' => 'Only return events or groups from a specific state. (All states if not provided.)',
            'pattern' => '^[A-Z]{2}$'
        ],
        'from' => [
            'type' => 'string',
            'description' => 'Only return events past the provided datetime. (Current time if not provided.)',
            'format' => 'date-time',
        ],
        'to' => [
            'type' => 'string',
            'description' => 'Only return events before the provided datetime. (No cutoff if not provided.)',
            'format' => 'date-time',
        ],
    ];

    static function rest_api_init() {
        register_rest_route('nashvilleccr/v1', '/mapdata', [
            'methods' => 'GET',
            'schema' => self::SCHEMA,
            'args' => self::ARGS_SCHEMA,
            'callback' => [self::class, 'get_mapdata'],
            'permission_callback' => '__return_true',
        ]);
    }

    static function get_mapdata(\WP_REST_Request $request) {
        $type = $request->get_param('type');
        $state = $request->get_param('state');
        $from = new \DateTime($request->get_param('from') ?? '');
        $to = $request->get_param('to');
        $to = is_null($to) ? $to : new \DateTime($to);

        $groups = [];
        $events = [];
        $locations = [];
        $contacts = [];

        if (!is_null($state)) {
            $state_query_args = [
                'post_type' => 'location',
                'posts_per_page' => -1,
                'post_status' => 'publish',
                'meta_query' => [
                    [
                        'key' => 'address',
                        'compare' => 'LIKE',
                        'value' => "%\"state_short\";s:2:\"{$state}\";%",
                    ]
                ],
                'fields' => 'ids',
            ];

            $state_query = new \WP_Query($state_query_args);

            $location_ids_in_state = $state_query->get_posts();
        }

        if (is_null($type) || $type === 'events') {
            $events_query_args = [
                'post_type' => 'event',
                'posts_per_page' => -1,
                'post_status' => 'publish',
                'meta_query' => [
                    [
                        'key' => '!schedule\_%\_from',
                        'compare' => '>=',
                        'value' => $from->format('Y-m-d H:i:s'),
                        'type' => 'DATETIME',
                    ]
                ],
            ];

            if (!is_null($to)) {
                $events_query_args['meta_query'][] = [
                    'key' => '!schedule\_%\_to',
                    'compare' => '<',
                    'value' => $to->format('Y-m-d H:i:s'),
                    'type' => 'DATETIME',
                ];
            }

            if (!is_null($state)) {
                $events_query_args['meta_query'][] = [
                    'key' => 'location',
                    'compare' => 'IN',
                    'value' => $location_ids_in_state,
                ];
            }

            $events_query = self::likebang_query($events_query_args);

            while ($events_query->have_posts()) {
                $events_query->the_post();
                self::add_results($events, $locations, $contacts);
            }
        }

        if (is_null($type) || $type === 'groups') {
            $groups_query_args = [
                'post_type' => 'group',
                'posts_per_page' => -1,
                'post_status' => 'publish',
                'meta_query' => [],
            ];

            if (!is_null($state)) {
                $groups_query_args['meta_query'][] = [
                    'key' => 'location',
                    'compare' => 'IN',
                    'value' => $location_ids_in_state,
                ];
            }

            $groups_query = new \WP_Query($groups_query_args);

            while ($groups_query->have_posts()) {
                $groups_query->the_post();
                self::add_results($groups, $locations, $contacts);
            }
        }

        wp_reset_postdata();

        return new \WP_REST_Response([
            'groups' => $groups,
            'events' => $events,
            'locations' => $locations,
            'contacts' => $contacts,
        ]);
    }

    static function add_results(&$eventsOrGroups, &$locations, &$contacts) {
        global $post;
        $location_id = get_field('location');

        $eventsOrGroups[$post->ID] = [
            'title' => $post->post_title,
            'link' => get_permalink($post),
            'location' => $location_id,
            'contacts' => get_field('contacts'),
        ];

        if (!isset($locations[$location_id])) {
            $location = get_post($location_id);
            $address = get_field('address', $location_id);

            $locations[$location->ID] = [
                'title' => $location->post_title,
                'link' => get_permalink($location),
                'lat' => $address['lat'],
                'lng' => $address['lng'],
            ];
        }

        foreach (get_field('contacts') as $contactId) {
            if (isset($contacts[$contactId])) {
                continue;
            }

            $contact = get_post($contactId);

            $contacts[$contact->ID] = [
                'title' => $contact->post_title,
                'link' => get_permalink($contact),
            ];
        }
    }
    
    static function preload_data() {
        $req = new \WP_REST_Request('GET', '/nashvilleccr/v1/mapdata');
        $res = rest_do_request($req);
        return $res->get_data();
    }

    static function likebang_query($args) {
        add_action('posts_where', [self::class, 'likebang_query_posts_where']);
        $res = new \WP_Query($args);
        remove_action('posts_where', [self::class, 'likebang_query_posts_where']);
        return $res;
    }

    static function likebang_query_posts_where($where) {
        $where = preg_replace(
            "/meta_key\s=\s'!((?:[^'\\\\]|\\\\.)*)'/",
            "meta_key like '\$1'",
            $where
        );

        return str_replace('\\\\', '\\', $where);
    }
}

MapBlock::load();
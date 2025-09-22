<?php namespace NashvilleCCR; defined('ABSPATH') || exit;

class MapAPI {
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
            'id' => ['type' => 'number'],
            'title' => ['type' => 'string'],
            'link' => ['type' => 'string'],
            'location' => ['type' => 'number'],
        ],
        'required' => ['id', 'title', 'link', 'location'],
        'additionalProperties' => false,
    ];

    const GROUP_SCHEMA = [
        'type' => 'object',
        'properties' => [
            'id' => ['type' => 'number'],
            'title' => ['type' => 'string'],
            'link' => ['type' => 'string'],
            'location' => ['type' => 'number'],
        ],
        'required' => ['id', 'title', 'link', 'location'],
        'additionalProperties' => false,
    ];

    const LOCATION_SCHEMA = [
        'type' => 'object',
        'properties' => [
            'id' => ['type' => 'number'],
            'title' => ['type' => 'string'],
            'link' => ['type' => 'string'],
            'lat' => ['type' => 'number'],
            'lng' => ['type' => 'number'],
        ],
        'required' => ['id', 'title', 'link', 'lat', 'lng'],
        'additionalProperties' => false,
    ];

    const CONTACT_SCHEMA = [
        'type' => 'object',
        'properties' => [
            'id' => ['type' => 'number'],
            'title' => ['type' => 'string'],
            'link' => ['type' => 'string'],
        ],
        'required' => ['id', 'title', 'link'],
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
            'description' => 'Only return events past the provided datetime. (No cutoff if not provided.)',
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

        if (is_null($type) || $type === 'events') {
            // load events
            // filter by datetime and state
        }

        if (is_null($type) || $type === 'groups') {
            // load groups
            // filter by datetime and state
        }

        return new \WP_REST_Response([
            'groups' => $groups,
            'events' => $events,
            'locations' => $locations,
            'contacts' => $contacts,
        ]);
    }
    
    static function preload_data() {
        $req = new \WP_REST_Request('GET', '/nashvilleccr/v1/mapdata');
        $res = rest_do_request($req);
        return $res->get_data();
    }
}

add_action('rest_api_init', [MapAPI::class, 'rest_api_init']);
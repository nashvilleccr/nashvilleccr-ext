<?php namespace NashvilleCCR; defined('ABSPATH') || exit;

class IpApi {
    static function load() {
        add_action('rest_api_init', [self::class, 'rest_api_init']);
    }

    const SCHEMA = [
        '$schema' => 'http://json-schema.org/draft-04/schema#',
        'title' => 'IP Info',
        'description' => "Returns geographic info about the request's IP address",
        'oneOf' => [
            self::SUCCESS_SCHEMA,
            self::ERROR_SCHEMA,
        ],
    ];

    const SUCCESS_SCHEMA = [
        'type' => 'object',
        'properties' => [
            'query' => ['type' => 'string'],
            'status' => ['type' => 'string', 'const' => 'success'],
            'country' => ['type' => 'string'],
            'countryCode' => ['type' => 'string'],
            'region' => ['type' => 'string'],
            'regionName' => ['type' => 'string'],
            'city' => ['type' => 'string'],
            'zip' => ['type' => 'string'],
            'lat' => ['type' => 'number'],
            'lon' => ['type' => 'number'],
            'timezone' => ['type' => 'string'],
            'isp' => ['type' => 'string'],
            'org' => ['type' => 'string'],
            'as' => ['type' => 'string'],
        ],
        'required' => [
            'query',
            'status',
            'country',
            'countryCode',
            'region',
            'regionName',
            'city',
            'zip',
            'lat',
            'lon',
            'timezone',
            'isp',
            'org',
            'as',
        ],
        'additionalProperties' => false,
    ];

    const ERROR_SCHEMA = [
        'type' => 'object',
        'properties' => [
            'query' => ['type' => 'string'],
            'status' => ['type' => 'string', 'const' => 'fail'],
            'message' => ['type' => 'string'],
        ],
        'required' => ['query', 'status', 'message'],
        'additionalProperties' => false,
    ];

    static function rest_api_init() {
        register_rest_route('nashvilleccr/v1', '/ipinfo', [
            'methods' => 'GET',
            'schema' => self::SCHEMA,
            'args' => [],
            'callback' => [self::class, 'get_ipinfo'],
            'permission_callback' => '__return_true',
        ]);
    }

    static function get_ipinfo(\WP_REST_Request $request) {
        $ip = $_SERVER['REMOTE_ADDR'];
        $cached = get_transient("nccrIpApi_ip_{$ip}");

        if ($cached) {
            return new \WP_REST_Response(json_decode($cached));
        }

        if (get_transient("nccrIpApi_timedOut")) {
            return new \WP_Error(
                'too_many_requests',
                'Rate limiting currently active.',
                [
                    'status' => 429,
                    'retry_after' => 60,
                ],
            );
        }

        $response = wp_remote_get("http://ip-api.com/json/{$ip}");

        if (is_wp_error($response)) {
            return $response;
        }

        $body = $response['body'];
        $headers = $response['headers'];
        $ttl = (int) $headers['x-ttl'];
        $rl = (int) $headers['x-rl'];

        if ($rl <= 0) {
            set_transient("nccrIpApi_timedOut", true, $ttl);
        }

        set_transient("nccrIpApi_ip_{$ip}", $body, 86400);

        return new \WP_REST_Response(json_decode($body));
    }
}
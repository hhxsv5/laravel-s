<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Whether to enable Prometheus collector
    |--------------------------------------------------------------------------
    | Default true.
    */
    'enable'                   => env('PROMETHEUS_ENABLE', true),

    /*
    |--------------------------------------------------------------------------
    | The name of the application
    |--------------------------------------------------------------------------
    | Default APP_NAME.
    */
    'application'              => env('PROMETHEUS_APP_NAME', env('APP_NAME', 'Laravel')),

    /*
    |--------------------------------------------------------------------------
    | The prefix of apcu keys
    |--------------------------------------------------------------------------
    |
    | Cannot contain any regular expression characters. Default "prom".
    |
    */
    'apcu_key_prefix'          => env('PROMETHEUS_APCU_KEY_PREFIX', 'prom'),

    /*
    |--------------------------------------------------------------------------
    | The separator of apcu keys
    |--------------------------------------------------------------------------
    |
    | Cannot contain any regular expression characters. Default "::".
    |
    */
    'apcu_key_separator'       => env('PROMETHEUS_APCU_KEY_SEPARATOR', '::'),

    /*
    |--------------------------------------------------------------------------
    | The max age(seconds) of apcu keys.
    |--------------------------------------------------------------------------
    |
    | It's TTL of apcu keys. Default 259200s(3 days).
    |
    */
    'apcu_key_max_age'         => env('PROMETHEUS_APCU_KEY_MAX_AGE', 259200),

    /*
    |--------------------------------------------------------------------------
    | The ignored status codes when collecting http requests.
    |--------------------------------------------------------------------------
    |
    | Default "400,404,405".
    |
    */
    'ignored_http_codes'       => array_flip(explode(',', env('PROMETHEUS_IGNORED_HTTP_CODES', '400,404,405'))),

    /*
    |--------------------------------------------------------------------------
    | The interval of collecting metrics.
    |--------------------------------------------------------------------------
    |
    | Default 10s.
    |
    */
    'collect_metrics_interval' => env('PROMETHEUS_COLLECT_METRICS_INTERVAL', 10),
];

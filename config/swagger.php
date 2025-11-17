<?php

return [
    /*
     * Paths to scan for OpenAPI attributes or annotations.
     * Default: app/Http/Controllers and app/Docs
     */
    'scan' => [
        'paths' => [
            base_path('app/Http/Controllers/Api'),
            base_path('app/Docs'),
        ],
        'exclude' => [
            // paths to exclude from scan
        ],
    ],

    /* Output path for generated specs */
    'output' => [
        'json' => storage_path('api-docs/api-docs.json'),
        'yaml' => storage_path('api-docs/api-docs.yaml'),
        'dir' => storage_path('api-docs'),
    ],

    /* Base path for API endpoints (used by docs/UI) */
    'base_path' => env('SWAGGER_BASE_PATH', '/api'),

    /* UI related settings */
    'ui' => [
        'display' => [
            'dark_mode' => env('SWAGGER_UI_DARK_MODE', false),
            'doc_expansion' => env('SWAGGER_UI_DOC_EXPANSION', 'none'),
            'filter' => env('SWAGGER_UI_FILTER', false),
        ],
        'authorization' => [
            'persist_authorization' => env('SWAGGER_UI_PERSIST_AUTH', false),
            'oauth2' => [
                'use_pkce_with_authorization_code_grant' => env('SWAGGER_UI_USE_PKCE', false),
            ],
        ],
    ],

    /* OpenAPI spec version */
    'open_api_spec_version' => env('SWAGGER_OPEN_API_SPEC_VERSION', '3.0.0'),

    /* Whether to always regenerate docs */
    'generate_always' => env('SWAGGER_GENERATE_ALWAYS', false),
];

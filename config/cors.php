<?php

return [
    'paths' => ['api/v1/vendor/public/*'],

    'allowed_methods' => ['GET', 'POST', 'OPTIONS'],

    'allowed_origins' => ['*'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['Content-Type', 'Accept', 'X-Client-Id'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,
];

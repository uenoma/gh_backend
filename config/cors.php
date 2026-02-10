<?php

return [
    'paths' => ['api/*'], // APIルートのみ許可
    'allowed_methods' => ['*'], // すべてのHTTPメソッドを許可
    'allowed_origins' => ['*'],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'], // すべてのヘッダーを許可
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => false,
];

<?php

declare(strict_types=1);

return [
    'name' => getenv('APP_NAME') ?: 'Sistema Cotizaciones API',
    'env' => getenv('APP_ENV') ?: 'local',
    'debug' => (getenv('APP_DEBUG') ?: 'false') === 'true',
];


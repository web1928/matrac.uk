<?php

/**
 * Application Configuration
 */

return [

    // Application name
    'name' => 'MatraC',

    // Application URL (from environment)
    'url' => getenv('APP_URL') ?: 'http://localhost',

    // Environment: local, production
    'env' => getenv('APP_ENV') ?: 'local',

    // Debug mode
    'debug' => getenv('APP_DEBUG') === 'true',

    // Timezone
    'timezone' => 'Europe/London',

    // Session configuration
    'session' => [
        'lifetime' => 120,  // minutes
        'cookie_name' => 'matrac_session',
    ],

];

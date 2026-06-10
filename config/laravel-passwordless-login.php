<?php

return [
    'login_route_name' => 'magic-login',
    'login_route_expires' => 60 * 3, // 3 hours // 60 * 24 * 2, // 2 days, 43200, // 30 days in minutes
    // Allow signed magic links to switch to the link user before redirecting.
    // The package default redirects already-authenticated browsers without logging in the link user,
    // which breaks admin-tested student verification links.
    'middleware' => ['web'],
];

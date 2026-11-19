<?php

return [
    'black_friday_2026' => [
        'enabled' => env('BLACK_FRIDAY_2026_ENABLED', false),
        'name' => 'Black Friday & Cyber Monday 2026',
        'timezone' => 'Africa/Johannesburg',
        'starts_at' => '2026-11-26 20:00:00',
        'ends_at' => '2026-12-01 00:00:00',
        'booking_fee' => 250,
        'state_path' => 'wordpress-promotions/black-friday-2026.json',
        'test_state_path' => 'wordpress-promotions/black-friday-2026-room-test.json',
        'test_room_id' => 4245,
        'residences' => [
            1592 => '49-jorissen',
            1623 => '80-jorissen',
            1108 => 'braamlofts',
            1653 => 'ymca',
            3871 => 'rennie-house-apartments',
            4130 => 'park-mews',
        ],
    ],
];

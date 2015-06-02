<?php

return [
    'sphinx' => [
        'sphinx' => [
            'dsn' => 'mysql:host=127.0.0.1;port=9306;',
            'username' => 'travis',
            'password' => '',
        ],
        'db' => [
            'dsn' => 'mysql:host=127.0.0.1;dbname=rocktest',
            'username' => 'rock',
            'password' => 'rock',
            'fixture' => __DIR__ . '/sphinx/source.sql',
        ],
    ],
];

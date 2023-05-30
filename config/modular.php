<?php
return [
    'path' => base_path() . '/app/Modules',
    'base_namespace' => 'App\Modules',
    'groupWithoutPrefix' => 'Pub',

    'groupMiddleware' => [
        'Admin' => [
            'web' => ['auth'],
            'api' => ['auth:api'],
        ],
//        'Pub' => [
//            'api' => ['api']
//        ]
    ],

    'modules' => [
        'Admin' => [
        ],
        'Pub' => [
        ]
    ],

    'withoutModel' => [
    ],

    'noAuth' => [
    ]
];

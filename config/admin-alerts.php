<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Admin alert categories
    |--------------------------------------------------------------------------
    |
    | Fixed catalog used for admin user assignment and email routing.
    | Admins with null/empty admin_alert_categories receive every category
    | until someone assigns them (safe rollout).
    |
    */

    'categories' => [
        'competition' => [
            'label' => 'Competitions',
            'description' => 'Registrations, ideas, prototypes, and phase backlog.',
        ],
        'clubs' => [
            'label' => 'STEM clubs',
            'description' => 'Club joins, pending membership requests, and school club activity.',
        ],
        'content' => [
            'label' => 'Labs & content',
            'description' => '3D models, VR experiments, projects, and weekly sessions.',
        ],
        'technical' => [
            'label' => 'Technical / support',
            'description' => 'Contact form messages and system/ops reminders.',
        ],
        'needs_attention' => [
            'label' => 'Needs attention',
            'description' => 'New users, unreviewed schools, and stale pending queues.',
        ],
    ],

];

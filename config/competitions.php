<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Eligibility completion reminders (admin → teacher)
    |--------------------------------------------------------------------------
    |
    | Max emails an admin may send per incomplete (draft) registration so
    | participants are nudged without being spammed.
    |
    */
    'max_eligibility_reminders' => (int) env('COMPETITION_MAX_ELIGIBILITY_REMINDERS', 3),

    /*
    |--------------------------------------------------------------------------
    | Shortlist reminders (admin → teacher)
    |--------------------------------------------------------------------------
    |
    | The first shortlist notification is sent once, when the idea is first
    | shortlisted and the admin confirms the message. Later nudges use this cap.
    |
    */
    'max_shortlist_reminders' => (int) env('COMPETITION_MAX_SHORTLIST_REMINDERS', 3),

];

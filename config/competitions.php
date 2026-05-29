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

];

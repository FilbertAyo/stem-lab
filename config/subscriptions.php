<?php

return [
  /*
  |--------------------------------------------------------------------------
  | Subscription payments
  |--------------------------------------------------------------------------
  |
  | When false, free-trial and individual package buttons are disabled on the
  | client dashboard until a payment provider is integrated.
  |
  */
  'payments_enabled' => env('SUBSCRIPTION_PAYMENTS_ENABLED', false),
];

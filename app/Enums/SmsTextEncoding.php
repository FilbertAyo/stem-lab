<?php

namespace App\Enums;

enum SmsTextEncoding: string
{
    case Gsm7 = 'gsm7';
    case Ucs2 = 'ucs2';
}

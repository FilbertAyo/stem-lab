<?php

namespace App\Services;

class StudentPinGenerator
{
    public const LENGTH = 6;

    public function generate(): string
    {
        $pin = '';

        for ($i = 0; $i < self::LENGTH; $i++) {
            $pin .= (string) random_int(0, 9);
        }

        return $pin;
    }
}

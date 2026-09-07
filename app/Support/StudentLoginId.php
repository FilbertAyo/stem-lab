<?php

namespace App\Support;

class StudentLoginId
{
    public static function normalize(string $value): string
    {
        return strtoupper(trim($value));
    }
}

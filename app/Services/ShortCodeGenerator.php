<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class ShortCodeGenerator
{
    public const CHARSET = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';

    public const DEFAULT_LENGTH = 6;

    private const MAX_ATTEMPTS = 10;

    public function generate(string $table, string $column, int $length = self::DEFAULT_LENGTH): string
    {
        for ($attempt = 0; $attempt < self::MAX_ATTEMPTS; $attempt++) {
            $code = $this->randomCode($length);

            $exists = DB::table($table)->where($column, $code)->exists();
            if (! $exists) {
                return $code;
            }
        }

        throw new \RuntimeException("Unable to generate a unique code for {$table}.{$column} after ".self::MAX_ATTEMPTS.' attempts.');
    }

    public function randomCode(int $length = self::DEFAULT_LENGTH): string
    {
        $charset = self::CHARSET;
        $maxIndex = strlen($charset) - 1;
        $code = '';

        for ($i = 0; $i < $length; $i++) {
            $code .= $charset[random_int(0, $maxIndex)];
        }

        return $code;
    }

    public function isValidFormat(string $code, int $length = self::DEFAULT_LENGTH): bool
    {
        if (strlen($code) !== $length) {
            return false;
        }

        return strspn($code, self::CHARSET) === $length;
    }
}

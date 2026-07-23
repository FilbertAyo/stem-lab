<?php

namespace App\Support;

class TanzaniaPhone
{
    /**
     * Normalize Tanzania mobile numbers to +255XXXXXXXXX.
     */
    public static function normalize(?string $phone): ?string
    {
        if ($phone === null) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if (str_starts_with($digits, '255') && strlen($digits) === 12) {
            $local = substr($digits, 3);
        } elseif (str_starts_with($digits, '0') && strlen($digits) === 10) {
            $local = substr($digits, 1);
        } elseif (strlen($digits) === 9) {
            $local = $digits;
        } else {
            return null;
        }

        if (! preg_match('/^[67][0-9]{8}$/', $local)) {
            return null;
        }

        return '+255' . $local;
    }

    /**
     * Build a WhatsApp chat URL (https://wa.me/255XXXXXXXXX) for a Tanzania mobile number.
     */
    public static function whatsappUrl(?string $phone): ?string
    {
        $normalized = self::normalize($phone);

        if ($normalized === null) {
            return null;
        }

        return 'https://wa.me/' . ltrim($normalized, '+');
    }
}

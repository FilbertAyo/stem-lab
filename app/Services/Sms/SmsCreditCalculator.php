<?php

namespace App\Services\Sms;

use App\Enums\SmsTextEncoding;

/**
 * Segment and credit quote for Soscom SMS.
 *
 * Soscom deducts one credit per segment per recipient. This is the only
 * segment calculator in the app: the compose preview and SmsNotifier both
 * call quote() so the number shown before send is the number attached to
 * the send. Limits live in config/sms.php (GSM-7 160/153, Unicode 70/67).
 */
class SmsCreditCalculator
{
    public static function gsmBasicCharacters(): string
    {
        return "@£\$¥èéùìòÇ\nØø\rÅåΔ_ΦΓΛΩΠΨΣΘΞ\x1BÆæßÉ !\"#¤%&'()*+,-./0123456789:;<=>?¡ABCDEFGHIJKLMNOPQRSTUVWXYZÄÖÑÜ§¿abcdefghijklmnopqrstuvwxyzäöñüà";
    }

    public static function gsmExtendedCharacters(): string
    {
        return "^{}\\[~]|€\f";
    }

    /**
     * @return array{gsmBasic: string, gsmExtended: string, gsmSingle: int, gsmConcat: int, unicodeSingle: int, unicodeConcat: int}
     */
    public static function clientRules(): array
    {
        return [
            'gsmBasic' => self::gsmBasicCharacters(),
            'gsmExtended' => self::gsmExtendedCharacters(),
            'gsmSingle' => self::limit('sms.segments.gsm_single', 160),
            'gsmConcat' => self::limit('sms.segments.gsm_concat', 153),
            'unicodeSingle' => self::limit('sms.segments.unicode_single', 70),
            'unicodeConcat' => self::limit('sms.segments.unicode_concat', 67),
        ];
    }

    /**
     * @return array{characters: int, units: int, encoding: string, segments: int, recipients: int, credits: int}
     */
    public static function quote(string $message, int $recipients = 1): array
    {
        $recipients = max(0, $recipients);
        $gsmUnits = self::gsmSeptetLength($message);

        if ($gsmUnits !== null) {
            $encoding = SmsTextEncoding::Gsm7;
            $units = $gsmUnits;
        } else {
            $encoding = SmsTextEncoding::Ucs2;
            $units = self::utf16Length($message);
        }

        $segments = self::segmentsFor($encoding, $units);

        return [
            'characters' => mb_strlen($message),
            'units' => $units,
            'encoding' => $encoding->value,
            'segments' => $segments,
            'recipients' => $recipients,
            'credits' => $segments * $recipients,
        ];
    }

    public static function previewSentence(string $message, int $recipients, bool $willSend): string
    {
        $quote = self::quote($message, 1);
        $credits = $willSend ? $quote['segments'] * max(0, $recipients) : 0;

        return 'This message is '.$quote['segments'].' SMS. Credits to be deducted: '.$credits;
    }

    private static function gsmSeptetLength(string $message): ?int
    {
        $basic = self::characterMap(self::gsmBasicCharacters());
        $extended = self::characterMap(self::gsmExtendedCharacters());
        $units = 0;

        foreach (mb_str_split($message) as $char) {
            if (isset($basic[$char])) {
                $units++;

                continue;
            }

            if (isset($extended[$char])) {
                $units += 2;

                continue;
            }

            return null;
        }

        return $units;
    }

    private static function utf16Length(string $message): int
    {
        $units = 0;

        foreach (mb_str_split($message) as $char) {
            $units += mb_ord($char) > 0xFFFF ? 2 : 1;
        }

        return $units;
    }

    private static function segmentsFor(SmsTextEncoding $encoding, int $units): int
    {
        if ($units <= 0) {
            return 0;
        }

        [$single, $concat] = match ($encoding) {
            SmsTextEncoding::Gsm7 => [
                self::limit('sms.segments.gsm_single', 160),
                self::limit('sms.segments.gsm_concat', 153),
            ],
            SmsTextEncoding::Ucs2 => [
                self::limit('sms.segments.unicode_single', 70),
                self::limit('sms.segments.unicode_concat', 67),
            ],
        };

        if ($units <= $single) {
            return 1;
        }

        return (int) ceil($units / $concat);
    }

    /**
     * @return array<string, true>
     */
    private static function characterMap(string $characters): array
    {
        $map = [];

        foreach (mb_str_split($characters) as $char) {
            $map[$char] = true;
        }

        return $map;
    }

    private static function limit(string $key, int $fallback): int
    {
        return max(1, (int) config($key, $fallback));
    }
}

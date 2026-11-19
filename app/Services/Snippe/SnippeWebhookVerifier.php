<?php

namespace App\Services\Snippe;

use Illuminate\Http\Request;
use RuntimeException;

class SnippeWebhookVerifier
{
    /**
     * @return array<string, mixed>
     */
    public function verify(Request $request): array
    {
        $secret = config('snippe.webhook_secret');

        if (! is_string($secret) || $secret === '') {
            throw new RuntimeException('SNIPPE_WEBHOOK_SECRET is not configured.');
        }

        $timestamp = (string) $request->header('X-Webhook-Timestamp', '');
        $signature = (string) $request->header('X-Webhook-Signature', '');
        $payload = $request->getContent();

        if ($timestamp === '' || $signature === '' || $payload === '') {
            throw new RuntimeException('Missing Snippe webhook signature headers or body.');
        }

        $tolerance = (int) config('snippe.webhook_tolerance_seconds', 300);
        $eventTime = (int) $timestamp;

        if ($eventTime <= 0 || abs(time() - $eventTime) > $tolerance) {
            throw new RuntimeException('Snippe webhook timestamp is outside the allowed tolerance.');
        }

        $expected = hash_hmac('sha256', $timestamp.'.'.$payload, $secret);

        if (! hash_equals($expected, $signature)) {
            throw new RuntimeException('Invalid Snippe webhook signature.');
        }

        $decoded = json_decode($payload, true);

        if (! is_array($decoded)) {
            throw new RuntimeException('Snippe webhook payload is not valid JSON.');
        }

        return $decoded;
    }
}

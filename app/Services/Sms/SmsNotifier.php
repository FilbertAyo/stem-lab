<?php

namespace App\Services\Sms;

use App\Support\TanzaniaPhone;
use RuntimeException;
use Throwable;

class SmsNotifier
{
    public function __construct(
        private readonly SoscomSmsClient $client,
    ) {}

    public function enabled(): bool
    {
        return (bool) config('sms.enabled', true) && filled(config('sms.api_key'));
    }

    /**
     * @return array{ok: bool, skipped?: bool, reason?: string, to?: string, sender_id?: string, response?: array<string, mixed>, error?: string}
     */
    public function send(?string $phone, string $message, ?string $senderId = null, ?string $idempotencyKey = null): array
    {
        if (! $this->enabled()) {
            return ['ok' => false, 'skipped' => true, 'reason' => 'sms_disabled'];
        }

        $normalized = TanzaniaPhone::normalize($phone);
        if ($normalized === null) {
            return ['ok' => false, 'skipped' => true, 'reason' => 'invalid_phone'];
        }

        $message = trim($message);
        if ($message === '') {
            return ['ok' => false, 'skipped' => true, 'reason' => 'empty_message'];
        }

        $sender = $this->resolveSender($senderId);

        try {
            $response = $this->client->send($sender, $normalized, $message, $idempotencyKey);

            return [
                'ok' => true,
                'to' => $normalized,
                'sender_id' => $sender,
                'response' => $response,
            ];
        } catch (Throwable $e) {
            report($e);

            return [
                'ok' => false,
                'to' => $normalized,
                'sender_id' => $sender,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * @return array{sms_balance: int|null, error?: string}
     */
    public function balance(): array
    {
        try {
            $data = $this->client->balance();

            return [
                'sms_balance' => isset($data['sms_balance']) ? (int) $data['sms_balance'] : null,
            ];
        } catch (Throwable $e) {
            report($e);

            return [
                'sms_balance' => null,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * @return array{sender_ids: list<array<string, mixed>>, error?: string}
     */
    public function allowedSenderIdsFromApi(): array
    {
        $allowed = array_map('strtoupper', CompetitionSmsTemplates::allowedSenders());

        try {
            $data = $this->client->senderIds();
            $raw = $data['sender_ids'] ?? [];
            if (! is_array($raw)) {
                $raw = [];
            }

            $filtered = [];
            foreach ($raw as $item) {
                $name = is_array($item)
                    ? (string) ($item['name'] ?? $item['sender_id'] ?? $item['id'] ?? '')
                    : (string) $item;

                $name = trim($name);
                if ($name === '') {
                    continue;
                }

                if (! in_array(strtoupper($name), $allowed, true)) {
                    continue;
                }

                $filtered[] = is_array($item)
                    ? array_merge($item, ['name' => $name])
                    : ['name' => $name];
            }

            // Preserve configured order even if API returns extras/missing.
            usort($filtered, function (array $a, array $b) use ($allowed): int {
                $ai = array_search(strtoupper((string) $a['name']), $allowed, true);
                $bi = array_search(strtoupper((string) $b['name']), $allowed, true);
                $ai = $ai === false ? 999 : $ai;
                $bi = $bi === false ? 999 : $bi;

                return $ai <=> $bi;
            });

            return ['sender_ids' => array_values($filtered)];
        } catch (Throwable $e) {
            report($e);

            return [
                'sender_ids' => array_map(fn (string $name) => ['name' => $name], CompetitionSmsTemplates::allowedSenders()),
                'error' => $e->getMessage(),
            ];
        }
    }

    private function resolveSender(?string $senderId): string
    {
        $allowed = CompetitionSmsTemplates::allowedSenders();
        $candidate = strtoupper(trim((string) ($senderId ?: CompetitionSmsTemplates::defaultSenderId())));

        foreach ($allowed as $sender) {
            if (strcasecmp($sender, $candidate) === 0) {
                return $sender;
            }
        }

        throw new RuntimeException('Sender ID is not allowed: '.$candidate);
    }
}

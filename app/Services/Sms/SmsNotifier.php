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
     * @return array{ok: bool, skipped?: bool, reason?: string, to?: string, sender_id?: string, response?: array<string, mixed>, error?: string, segments?: int, credits?: int, encoding?: string}
     */
    public function send(?string $phone, string $message, ?string $senderId = null, ?string $idempotencyKey = null): array
    {
        $message = trim($message);
        $quote = $message === '' ? null : SmsCreditCalculator::quote($message, 1);

        if (! $this->enabled()) {
            return $this->withQuote(['ok' => false, 'skipped' => true, 'reason' => 'sms_disabled'], $quote);
        }

        $normalized = TanzaniaPhone::normalize($phone);
        if ($normalized === null) {
            return $this->withQuote(['ok' => false, 'skipped' => true, 'reason' => 'invalid_phone'], $quote);
        }

        if ($message === '') {
            return $this->withQuote(['ok' => false, 'skipped' => true, 'reason' => 'empty_message'], $quote);
        }

        $sender = $this->resolveSender($senderId);

        try {
            $response = $this->client->send($sender, $normalized, $message, $idempotencyKey);

            return $this->withQuote([
                'ok' => true,
                'to' => $normalized,
                'sender_id' => $sender,
                'response' => $response,
            ], $quote);
        } catch (Throwable $e) {
            report($e);

            return $this->withQuote([
                'ok' => false,
                'to' => $normalized,
                'sender_id' => $sender,
                'error' => $e->getMessage(),
            ], $quote);
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

    /**
     * @param  array<string, mixed>  $result
     * @param  array{segments: int, credits: int, encoding: string}|null  $quote
     * @return array<string, mixed>
     */
    private function withQuote(array $result, ?array $quote): array
    {
        if ($quote === null) {
            return $result;
        }

        return array_merge($result, [
            'segments' => $quote['segments'],
            'credits' => $quote['credits'],
            'encoding' => $quote['encoding'],
        ]);
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

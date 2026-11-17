<?php

namespace App\Services\Sms;

use App\Support\TanzaniaPhone;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class SoscomSmsClient
{
    public function balance(): array
    {
        return $this->get('/balance');
    }

    public function senderIds(): array
    {
        return $this->get('/sender-ids');
    }

    /**
     * @param  string|array<int, string>  $to
     * @return array<string, mixed>
     */
    public function send(string $senderId, string|array $to, string $message, ?string $idempotencyKey = null): array
    {
        $recipients = is_array($to) ? $to : [$to];
        $normalized = [];

        foreach ($recipients as $recipient) {
            $phone = TanzaniaPhone::normalize((string) $recipient);
            if ($phone === null) {
                throw new RuntimeException('Invalid Tanzanian phone number: '.$recipient);
            }
            $normalized[] = $phone;
        }

        $payload = [
            'sender_id' => $senderId,
            'to' => count($normalized) === 1 ? $normalized[0] : $normalized,
            'message' => $message,
        ];

        return $this->post('/sms/send', $payload, $idempotencyKey);
    }

    /**
     * @return array<string, mixed>
     */
    private function get(string $path): array
    {
        return $this->request('get', $path);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function post(string $path, array $payload, ?string $idempotencyKey = null): array
    {
        return $this->request('post', $path, $payload, $idempotencyKey);
    }

    /**
     * @param  array<string, mixed>|null  $payload
     * @return array<string, mixed>
     */
    private function request(string $method, string $path, ?array $payload = null, ?string $idempotencyKey = null): array
    {
        $apiKey = (string) config('sms.api_key');
        if ($apiKey === '') {
            throw new RuntimeException('Soscom SMS API key is not configured.');
        }

        $url = config('sms.base_url').'/'.ltrim($path, '/');

        $pending = Http::timeout((int) config('sms.timeout', 15))
            ->acceptJson()
            ->withToken($apiKey)
            ->withHeaders([
                'X-Api-Key' => $apiKey,
            ]);

        if (filled($idempotencyKey)) {
            $pending = $pending->withHeaders([
                'Idempotency-Key' => $idempotencyKey,
            ]);
        }

        try {
            $response = $method === 'get'
                ? $pending->get($url)
                : $pending->asJson()->post($url, $payload ?? []);

            $response->throw();
        } catch (RequestException $e) {
            $body = $e->response?->json();
            $message = is_array($body)
                ? (string) ($body['message'] ?? $body['error'] ?? $e->getMessage())
                : $e->getMessage();

            throw new RuntimeException('Soscom SMS request failed: '.$message, previous: $e);
        }

        $json = $response->json();

        return is_array($json) ? $json : [];
    }
}

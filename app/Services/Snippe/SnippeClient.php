<?php

namespace App\Services\Snippe;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class SnippeClient
{
    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function createSession(array $payload, ?string $idempotencyKey = null): array
    {
        $request = $this->http();

        if ($idempotencyKey) {
            $request = $request->withHeaders([
                'Idempotency-Key' => $idempotencyKey,
            ]);
        }

        try {
            $response = $request->post('/api/v1/sessions', $payload)->throw();
        } catch (RequestException $exception) {
            throw new RuntimeException(
                'Snippe session creation failed: '.$exception->response?->body(),
                previous: $exception
            );
        }

        $body = $response->json();

        if (! is_array($body) || ! isset($body['data']) || ! is_array($body['data'])) {
            throw new RuntimeException('Unexpected Snippe session response.');
        }

        return $body['data'];
    }

    /**
     * @return array<string, mixed>
     */
    public function getSession(string $reference): array
    {
        try {
            $response = $this->http()
                ->get('/api/v1/sessions/'.$reference)
                ->throw();
        } catch (RequestException $exception) {
            throw new RuntimeException(
                'Snippe session lookup failed: '.$exception->response?->body(),
                previous: $exception
            );
        }

        $body = $response->json();

        if (! is_array($body) || ! isset($body['data']) || ! is_array($body['data'])) {
            throw new RuntimeException('Unexpected Snippe session lookup response.');
        }

        return $body['data'];
    }

    private function http(): PendingRequest
    {
        $apiKey = config('snippe.api_key');

        if (! is_string($apiKey) || $apiKey === '') {
            throw new RuntimeException('SNIPPE_API_KEY is not configured.');
        }

        return Http::baseUrl(rtrim((string) config('snippe.base_url'), '/'))
            ->withToken($apiKey)
            ->acceptJson()
            ->asJson()
            ->timeout((int) config('snippe.http_timeout', 30));
    }
}

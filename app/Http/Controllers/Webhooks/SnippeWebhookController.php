<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Services\Snippe\SnippeWebhookProcessor;
use App\Services\Snippe\SnippeWebhookVerifier;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Throwable;

class SnippeWebhookController extends Controller
{
    public function __invoke(
        Request $request,
        SnippeWebhookVerifier $verifier,
        SnippeWebhookProcessor $processor,
    ): Response {
        try {
            $event = $verifier->verify($request);
            $processor->process($event);
        } catch (Throwable $exception) {
            Log::warning('Snippe webhook rejected or failed.', [
                'message' => $exception->getMessage(),
            ]);

            return response($exception->getMessage(), 400);
        }

        return response('OK', 200);
    }
}

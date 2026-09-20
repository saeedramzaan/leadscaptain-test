<?php

namespace App\Infrastructure\Leadscaptain;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

final class LeadscaptainClient
{
    public function getLeads(
        int $page = 1,
        int $limit = 100,
        array $filters = [],
    ): array {
        $response = $this->request()
            ->retry(
                config('leadscaptain.retry_times'),
                config('leadscaptain.retry_sleep'),
                function ($exception, $request) {
                    if ($exception instanceof \Illuminate\Http\Client\RequestException) {
                        return $exception->response->tooManyRequests()
                            || $exception->response->serverError();
                    }

                    return $exception instanceof \Illuminate\Http\Client\ConnectionException;
                },
                false
            )
            ->get('/leads', array_merge([
                'page' => $page,
                'limit' => $limit,
            ], $filters));

        if ($response->failed()) {
            throw new RuntimeException(
                "Leadscaptain API request failed with status {$response->status()}."
            );
        }

        $data = $response->json();

        if (
            !is_array($data)
            || !isset($data['data'])
            || !is_array($data['data'])
            || !isset($data['total_pages'])
            || !is_int($data['total_pages'])
        ) {
            throw new RuntimeException(
                'Leadscaptain API returned an invalid response.'
            );
        }

        return $data;
    }

    private function request(): PendingRequest
    {
        return Http::baseUrl(config('leadscaptain.base_url'))
            ->timeout(config('leadscaptain.timeout'))
            ->withHeaders([
                'X-API-Token' => config('leadscaptain.api_token'),
                'Accept' => 'application/json',
            ]);
    }
}
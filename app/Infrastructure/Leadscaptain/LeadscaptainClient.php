<?php

namespace App\Infrastructure\Leadscaptain;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use InvalidArgumentException;

final class LeadscaptainClient
{
    public function getLeads(
        int $page = 1,
        int $limit = 100,
        array $filters = [],
    ): array {
        if ($page < 1) {
            throw new InvalidArgumentException('Page must be at least 1.');
        }
        
        if ($limit < 1) {
            throw new InvalidArgumentException('Limit must be at least 1.');
        }

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
            || array_filter($data['data'], fn ($lead) => !is_array($lead)) !== []
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
<?php

namespace Tests\Unit;

use App\Infrastructure\Leadscaptain\LeadscaptainClient;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;


class LeadscaptainClientTest extends TestCase
{
    public function test_it_fetches_leads_successfully(): void
    {
        Http::fake([
            'https://api.leadscaptain.com/leads*' => Http::response([
                'data' => [
                    [
                        'key' => 'lv596u',
                        'leadscaptain_public_identifier' => 'public-123',
                        'leadscaptain_id' => 'lead-123',
                        'leadscaptain_member_id' => 123,
                        'emails' => [
                            'john@example.com',
                        ],
                        'personal_emails' => [],
                        'phones' => [
                            '+123456789',
                        ],
                        'first_name' => 'John',
                        'last_name' => 'Doe',
                        'team' => 'Engineering',
                        'hierarchy' => 'Manager',
                        'persona' => 'Technical',
                        'gender' => 'male',
                        'country_code' => 'US',
                        'summary' => 'Test lead',
                        'industry_name' => 'Technology',
                        'birth_year' => '1990',
                        'marvin_searches' => [],
                        'position_started_at' => '2024-01-01',
                        'position_title' => 'Engineering Manager',
                        'position_location' => 'New York',
                        'position_description' => 'Manages engineering teams',
                        'company_name' => 'Example Inc',
                        'company_leadscaptain_id' => 'company-123',
                        'company_leadscaptain_universal_name' => 'example-inc',
                        'company_salesforce_id' => null,
                        'company_spendesk_id' => null,
                        'company_hubspot_id' => null,
                        'skills' => [
                            'PHP',
                            'Laravel',
                        ],
                        'languages' => [
                            'English',
                        ],
                        'schools' => [],
                        'external_searches' => [],
                        'headline' => 'Engineering Manager',
                        'linkedin_source' => true,
                        'linkedin_url' => 'https://linkedin.com/in/johndoe',
                        'sales_navigator_source' => false,
                        'email_status' => 'verified',
                        'email_last_checked' => '2026-09-17',
                    ],
                ],
                'page' => 1,
                'limit' => 100,
                'total' => 1,
                'total_pages' => 1,
            ], 200),
        ]);

        $client = app(LeadscaptainClient::class);

        $result = $client->getLeads(
            page: 1,
            limit: 100,
        );

        $this->assertSame(1, $result['page']);
        $this->assertSame(100, $result['limit']);
        $this->assertSame(1, $result['total']);
        $this->assertSame(1, $result['total_pages']);

        $this->assertCount(1, $result['data']);
        $this->assertSame(
            'lead-123',
            $result['data'][0]['leadscaptain_id']
        );

        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.leadscaptain.com/leads?page=1&limit=100'
                && $request->header('X-API-Token')[0] === ''
                && $request->header('Accept')[0] === 'application/json';
        });
    }

    public function test_it_rejects_a_response_when_data_is_not_an_array(): void
    {
    Http::fake([
        '*leads*' => Http::response([
            'total_pages' => 1,
            'data' => 'invalid-data',
        ], 200),
    ]);

    $client = app(LeadscaptainClient::class);

    $this->expectException(\RuntimeException::class);
    $this->expectExceptionMessage(
        'Leadscaptain API returned an invalid response.'
    );

    $client->getLeads();
    }

    public function test_it_throws_exception_when_api_returns_server_error(): void
    {
        Http::fake([
            'https://api.leadscaptain.com/leads*' => Http::response([
                'detail' => 'Internal server error',
            ], 500),
        ]);
    
        $client = app(LeadscaptainClient::class);
    
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Leadscaptain API request failed with status 500'
        );
    
        $client->getLeads();
    }


    public function test_it_retries_when_api_temporarily_returns_server_error(): void
    {
        Http::fakeSequence()
            ->push([
                'detail' => 'Internal server error',
            ], 500)
            ->push([
                'detail' => 'Internal server error',
            ], 500)
            ->push([
                'data' => [],
                'page' => 1,
                'limit' => 100,
                'total' => 0,
                'total_pages' => 1,
            ], 200);

        $client = app(LeadscaptainClient::class);

        $result = $client->getLeads();

        $this->assertSame(1, $result['page']);
        $this->assertSame(0, $result['total']);

        Http::assertSentCount(3);
    }


    public function test_it_retries_when_api_returns_too_many_requests(): void
    {
    Http::fakeSequence()
        ->push([
            'detail' => 'Too many requests',
        ], 429)
        ->push([
            'detail' => 'Too many requests',
        ], 429)
        ->push([
            'data' => [],
            'page' => 1,
            'limit' => 100,
            'total' => 0,
            'total_pages' => 1,
        ], 200);

    $client = app(LeadscaptainClient::class);

    $result = $client->getLeads();

    $this->assertSame(1, $result['page']);
    $this->assertSame(0, $result['total']);

    Http::assertSentCount(3);
    }

    public function test_it_does_not_retry_when_api_returns_unauthorized(): void
{
    Http::fakeSequence()
        ->push(['detail' => 'Unauthorized'], 401);

    $client = app(LeadscaptainClient::class);

    $this->expectException(RuntimeException::class);
    $this->expectExceptionMessage(
        'Leadscaptain API request failed with status 401'
    );

    try {
        $client->getLeads();
    } finally {
        Http::assertSentCount(1);
    }
}

    public function test_it_retries_when_connection_fails(): void
{
    Http::fakeSequence()
        ->pushFailedConnection()
        ->pushFailedConnection()
        ->push([
            'data' => [],
            'page' => 1,
            'limit' => 100,
            'total' => 0,
            'total_pages' => 1,
        ], 200);

    $client = app(LeadscaptainClient::class);

    $result = $client->getLeads();

    $this->assertSame(1, $result['page']);
    $this->assertSame(0, $result['total']);

    Http::assertSentCount(3);
}

public function test_it_fails_after_retries_are_exhausted(): void
{
    Http::fakeSequence()
        ->push(['detail' => 'Internal server error'], 500)
        ->push(['detail' => 'Internal server error'], 500)
        ->push(['detail' => 'Internal server error'], 500)
        ->push(['detail' => 'Internal server error'], 500)
        ->push(['detail' => 'Internal server error'], 500)
        ->push(['detail' => 'Internal server error'], 500);

    $client = app(LeadscaptainClient::class);

    $this->expectException(RuntimeException::class);
    $this->expectExceptionMessage(
        'Leadscaptain API request failed with status 500'
    );

    $client->getLeads();

    Http::assertSentCount(6);
}

public function test_it_rejects_a_response_when_total_pages_is_missing(): void
{
    Http::fake([
        '*leads*' => Http::response([
            'data' => [],
        ], 200),
    ]);

    $client = app(LeadscaptainClient::class);

    $this->expectException(\RuntimeException::class);
    $this->expectExceptionMessage(
        'Leadscaptain API returned an invalid response.'
    );

    $client->getLeads();
}

public function test_it_rejects_a_response_when_total_pages_is_not_an_integer(): void
{
    Http::fake([
        '*leads*' => Http::response([
            'total_pages' => '10',
            'data' => [],
        ], 200),
    ]);

    $client = app(LeadscaptainClient::class);

    $this->expectException(\RuntimeException::class);
    $this->expectExceptionMessage(
        'Leadscaptain API returned an invalid response.'
    );

    $client->getLeads();
}

    public function test_it_rejects_a_response_when_a_lead_is_not_an_array(): void
    {
        Http::fake([
            "*leads*" => Http::response([
                "total_pages" => 1,
                "data" => [
                    [
                        "leadscaptain_id" => "lead-123",
                    ],
                    "invalid-lead",
                ],
            ], 200),
        ]);

        $client = app(LeadscaptainClient::class);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            "Leadscaptain API returned an invalid response."
        );

        $client->getLeads();
    }
}

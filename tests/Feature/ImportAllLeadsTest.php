<?php

namespace Tests\Feature;

use App\Application\Lead\ImportLeads;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ImportAllLeadsTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_imports_all_pages_of_leads(): void
    {
        Http::fake([
            'https://api.leadscaptain.com/leads?page=1*' => Http::response([
                'data' => [
                    [
                        'leadscaptain_id' => 'lead-001',
                        'first_name' => 'John',
                        'last_name' => 'Doe',
                    ],
                    [
                        'leadscaptain_id' => 'lead-002',
                        'first_name' => 'Jane',
                        'last_name' => 'Smith',
                    ],
                ],
                'page' => 1,
                'limit' => 2,
                'total' => 5,
                'total_pages' => 3,
            ], 200),

            'https://api.leadscaptain.com/leads?page=2*' => Http::response([
                'data' => [
                    [
                        'leadscaptain_id' => 'lead-003',
                        'first_name' => 'Bob',
                        'last_name' => 'Brown',
                    ],
                    [
                        'leadscaptain_id' => 'lead-004',
                        'first_name' => 'Alice',
                        'last_name' => 'Jones',
                    ],
                ],
                'page' => 2,
                'limit' => 2,
                'total' => 5,
                'total_pages' => 3,
            ], 200),

            'https://api.leadscaptain.com/leads?page=3*' => Http::response([
                'data' => [
                    [
                        'leadscaptain_id' => 'lead-005',
                        'first_name' => 'Mike',
                        'last_name' => 'Wilson',
                    ],
                ],
                'page' => 3,
                'limit' => 2,
                'total' => 5,
                'total_pages' => 3,
            ], 200),
        ]);

        $importLeads = app(ImportLeads::class);

        $importLeads->importAll(
            limit: 2,
        );

        $this->assertDatabaseCount('leads', 5);

        $this->assertDatabaseHas('leads', [
            'leadscaptain_id' => 'lead-001',
            'first_name' => 'John',
        ]);

        $this->assertDatabaseHas('leads', [
            'leadscaptain_id' => 'lead-003',
            'first_name' => 'Bob',
        ]);

        $this->assertDatabaseHas('leads', [
            'leadscaptain_id' => 'lead-005',
            'first_name' => 'Mike',
        ]);

        Http::assertSentCount(3);
    }
}
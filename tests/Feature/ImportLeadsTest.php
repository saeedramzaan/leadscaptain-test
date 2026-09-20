<?php

namespace Tests\Feature;

use App\Application\Lead\ImportLeads;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ImportLeadsTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_imports_a_page_of_leads(): void
    {
        Http::fake([
            'https://api.leadscaptain.com/leads*' => Http::response([
                'data' => [
                    [
                        'leadscaptain_id' => 'lead-123',
                        'first_name' => 'John',
                        'last_name' => 'Doe',
                        'company_name' => 'Example Inc',
                        'position_title' => 'Engineering Manager',
                        'emails' => ['john@example.com'],
                        'phones' => ['+123456789'],
                        'skills' => ['PHP', 'Laravel'],
                    ],
                ],
                'page' => 1,
                'limit' => 100,
                'total' => 1,
                'total_pages' => 1,
            ], 200),
        ]);

        $importLeads = app(ImportLeads::class);

        $importLeads->importPage(
            page: 1,
            limit: 100,
        );

        $this->assertDatabaseCount('leads', 1);

        $this->assertDatabaseHas('leads', [
            'leadscaptain_id' => 'lead-123',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'company_name' => 'Example Inc',
            'position_title' => 'Engineering Manager',
        ]);
    }
}
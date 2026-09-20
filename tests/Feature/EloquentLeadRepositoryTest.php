<?php

namespace Tests\Feature;

use App\Domain\Lead\DTOs\LeadData;
use App\Infrastructure\Lead\EloquentLeadRepository;
use App\Models\Lead;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EloquentLeadRepositoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_upserts_leads_without_creating_duplicates(): void
    {
        $repository = app(EloquentLeadRepository::class);

        $lead = new LeadData(
            leadscaptainId: 'lead-123',
            firstName: 'John',
            lastName: 'Doe',
            companyName: 'Example Inc',
        );

        $repository->upsertMany([$lead]);

        $updatedLead = new LeadData(
            leadscaptainId: 'lead-123',
            firstName: 'Jane',
            lastName: 'Doe',
            companyName: 'Updated Inc',
        );

        $repository->upsertMany([$updatedLead]);

        $this->assertDatabaseCount('leads', 1);

        //Is that one lead now updated to Jane / Updated Inc?”
        $this->assertDatabaseHas('leads', [
            'leadscaptain_id' => 'lead-123',
            'first_name' => 'Jane',
            'company_name' => 'Updated Inc',
        ]);
    }
}
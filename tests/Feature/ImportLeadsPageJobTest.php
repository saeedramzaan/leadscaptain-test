<?php

namespace Tests\Feature;

use App\Application\Lead\ImportLeads;
use App\Jobs\ImportLeadsPageJob;
use Mockery;
use Tests\TestCase;

class ImportLeadsPageJobTest extends TestCase
{
    public function test_it_imports_the_requested_page(): void
    {
        $importLeads = Mockery::mock(ImportLeads::class);

        $importLeads
            ->shouldReceive('importPage')
            ->once()
            ->with(
                3,
                50,
                ['country_code' => 'LK'],
            );

        $job = new ImportLeadsPageJob(
            page: 3,
            limit: 50,
            filters: ['country_code' => 'LK'],
        );

        $job->handle($importLeads);
    }

    public function test_it_has_queue_retry_configuration(): void
    {
    $job = new ImportLeadsPageJob(page: 1);

    $this->assertSame(3, $job->tries);
    $this->assertSame(30, $job->backoff);
    }

    public function test_it_propagates_import_failure_to_the_queue(): void
    {
    $importLeads = Mockery::mock(ImportLeads::class);

    $importLeads
        ->shouldReceive('importPage')
        ->once()
        ->with(
            3,
            50,
            ['country_code' => 'LK'],
        )
        ->andThrow(new \RuntimeException('Leadscaptain API failed.'));

    $job = new ImportLeadsPageJob(
        page: 3,
        limit: 50,
        filters: ['country_code' => 'LK'],
    );

    $this->expectException(\RuntimeException::class);

    $job->handle($importLeads);
    
    }

}
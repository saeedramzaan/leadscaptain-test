<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Application\Lead\ImportLeads;
use App\Jobs\ImportAllLeadsJob;
use App\Jobs\ImportLeadsPageJob;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ImportAllLeadsJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_dispatches_a_page_job_for_every_api_page(): void
    {
        Queue::fake();

        Http::fake([
            '*leads*' => Http::response([
                'total_pages' => 5,
                'data' => [
                    [
                        'leadscaptain_id' => 'lead-page-1',
                        'first_name' => 'John',
                        'last_name' => 'Doe',
                        'company_name' => 'Example Inc',
                        'position_title' => 'Engineering Manager',
                    ],
                ],
            ], 200),
        ]);

        $job = new ImportAllLeadsJob(
            limit: 50,
            filters: ['country_code' => 'LK'],
        );

        $job->handle(
            app(ImportLeads::class)
        );

        $this->assertDatabaseHas('leads', [
            'leadscaptain_id' => 'lead-page-1',
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        Queue::assertPushed(ImportLeadsPageJob::class, 4);

        Queue::assertPushed(
            ImportLeadsPageJob::class,
            fn (ImportLeadsPageJob $pageJob) =>
                $pageJob->page === 2 &&
                $pageJob->limit === 50 &&
                $pageJob->filters === ['country_code' => 'LK'],
        );

        Queue::assertPushed(
            ImportLeadsPageJob::class,
            fn (ImportLeadsPageJob $pageJob) =>
                $pageJob->page === 5 &&
                $pageJob->limit === 50 &&
                $pageJob->filters === ['country_code' => 'LK'],
        );
    }

    public function test_it_has_queue_retry_configuration(): void
    {
        $job = new ImportAllLeadsJob();

    $this->assertSame(3, $job->tries);
    $this->assertSame(30, $job->backoff);
}
}

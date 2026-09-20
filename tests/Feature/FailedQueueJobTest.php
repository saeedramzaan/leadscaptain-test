<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\Jobs\PermanentlyFailingJob;
use Tests\TestCase;

class FailedQueueJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_permanently_failing_job_is_recorded_in_failed_jobs(): void
    {
        PermanentlyFailingJob::dispatch()
            ->onQueue('test-failures');

        $this->assertDatabaseCount('jobs', 1);

        for ($attempt = 1; $attempt <= 3; $attempt++) {
            try {
                $this->artisan('queue:work', [
                    'connection' => 'database',
                    '--queue' => 'test-failures',
                    '--once' => true,
                ]);
            } catch (\Throwable $exception) {
                // The worker surfaces the failed job exception to the test process.
                // Laravel has already handled the queue attempt.
            }
        }

        $this->assertDatabaseCount('jobs', 0);

        $this->assertDatabaseCount('failed_jobs', 1);

        $this->assertDatabaseHas('failed_jobs', [
            'queue' => 'test-failures',
        ]);
    }
}
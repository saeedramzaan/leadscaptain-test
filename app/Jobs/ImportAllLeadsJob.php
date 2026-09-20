<?php

namespace App\Jobs;

use App\Application\Lead\ImportLeads;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ImportAllLeadsJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3; // Maximum queue attempts for this job.

    public int $backoff = 30; // Delay between queue attempts.

    public function __construct(
        public int $limit = 100,
        public array $filters = [],
    ) {
    }

    public function handle(ImportLeads $importLeads): void
    {
        $response = $importLeads->importPage(
            page: 1,
            limit: $this->limit,
            filters: $this->filters,
        );

        $totalPages = (int) ($response['total_pages'] ?? 1);

        for ($page = 2; $page <= $totalPages; $page++) {
            ImportLeadsPageJob::dispatch( // // Add this page job to the queue for a worker to execute
                page: $page,
                limit: $this->limit,
                filters: $this->filters,
            );
        }
    }
}
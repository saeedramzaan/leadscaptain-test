<?php

namespace App\Jobs;

use App\Application\Lead\ImportLeads;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;



class ImportLeadsPageJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3; // Maximum queue attempts for this job.

    public int $backoff = 30; // Delay between queue attempts.

    public function __construct(
        public int $page,
        public int $limit = 100,
        public array $filters = [],
    ) {
    }

    public function handle(ImportLeads $importLeads): void
    {
        $importLeads->importPage(
            page: $this->page,
            limit: $this->limit,
            filters: $this->filters,
        );
    }


}
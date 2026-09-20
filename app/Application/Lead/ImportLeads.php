<?php

namespace App\Application\Lead;

use App\Domain\Lead\Repositories\LeadRepository;
use App\Infrastructure\Leadscaptain\LeadscaptainClient;

class ImportLeads
{
    public function __construct(
        private readonly LeadscaptainClient $client,
        private readonly LeadRepository $repository,
        private readonly LeadDataMapper $mapper,
    ) {
    }

    public function importPage(
        int $page = 1,
        int $limit = 100,
        array $filters = [],
    ): array {
        $response = $this->client->getLeads(
            page: $page,
            limit: $limit,
            filters: $filters,
        );
    
        $leads = array_map(
            fn (array $lead) => $this->mapper->map($lead),
            $response['data'] ?? [],
        );
    
        $this->repository->upsertMany($leads);
    
        return $response;
    }

    public function importAll(
        int $limit = 100,
        array $filters = [],
    ): void {
        $page = 1;

        do {
            $response = $this->importPage(
                page: $page,
                limit: $limit,
                filters: $filters,
            );
        
            $totalPages = (int) ($response['total_pages'] ?? $page);
        
            $page++;
        } while ($page <= $totalPages);
    }
}
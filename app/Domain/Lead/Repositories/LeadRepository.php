<?php

namespace App\Domain\Lead\Repositories;

use App\Domain\Lead\DTOs\LeadData;

interface LeadRepository
{
    /**
     * @param LeadData[] $leads
     */
    public function upsertMany(array $leads): void;
}
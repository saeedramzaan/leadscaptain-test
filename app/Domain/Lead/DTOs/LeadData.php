<?php

namespace App\Domain\Lead\DTOs;

final readonly class LeadData
{
    public function __construct(
        public string $leadscaptainId,
        public ?string $leadscaptainPublicIdentifier = null,
        public ?string $leadscaptainMemberId = null,

        public ?string $firstName = null,
        public ?string $lastName = null,
        public ?string $gender = null,
        public ?string $countryCode = null,
        public ?string $persona = null,
        public ?string $summary = null,
        public ?string $birthYear = null,

        public ?string $positionTitle = null,
        public ?string $positionLocation = null,
        public ?string $positionDescription = null,
        public ?string $positionStartedAt = null,

        public ?string $companyName = null,
        public ?string $companyLeadscaptainId = null,
        public ?string $companyLeadscaptainUniversalName = null,
        public ?string $companySalesforceId = null,
        public ?string $companySpendeskId = null,
        public ?string $companyHubspotId = null,
        public ?string $industryName = null,

        public ?string $linkedinUrl = null,
        public bool $linkedinSource = false,
        public bool $salesNavigatorSource = false,
        public ?string $emailStatus = null,
        public ?string $emailLastChecked = null,

        public array $emails = [],
        public array $personalEmails = [],
        public array $phones = [],
        public array $marvinSearches = [],
        public array $skills = [],
        public array $languages = [],
        public array $schools = [],
        public array $externalSearches = [],
    ) {}
}
<?php

namespace App\Infrastructure\Lead;

use App\Domain\Lead\DTOs\LeadData;
use App\Domain\Lead\Repositories\LeadRepository;
use App\Models\Lead;
use Illuminate\Support\Carbon;
use JsonException;

final class EloquentLeadRepository implements LeadRepository
{
    /**
     * @param LeadData[] $leads
     *
     * @throws JsonException
     */
    public function upsertMany(array $leads): void
    {
        if ($leads === []) {
            return;
        }

        $now = Carbon::now();

        $rows = array_map(
            fn (LeadData $lead) => $this->toRow($lead, $now),
            $leads
        );

        Lead::upsert(
            $rows,
            ['leadscaptain_id'],
            [
                'leadscaptain_public_identifier',
                'leadscaptain_member_id',
                'first_name',
                'last_name',
                'gender',
                'country_code',
                'persona',
                'summary',
                'birth_year',
                'position_title',
                'position_location',
                'position_description',
                'position_started_at',
                'company_name',
                'company_leadscaptain_id',
                'company_leadscaptain_universal_name',
                'company_salesforce_id',
                'company_spendesk_id',
                'company_hubspot_id',
                'industry_name',
                'linkedin_url',
                'linkedin_source',
                'sales_navigator_source',
                'email_status',
                'email_last_checked',
                'emails',
                'personal_emails',
                'phones',
                'marvin_searches',
                'skills',
                'languages',
                'schools',
                'external_searches',
                'updated_at',
            ]
        );
    }

    /**
     * @throws JsonException
     */
    private function toRow(LeadData $lead, Carbon $now): array
    {
        return [
            'leadscaptain_id' => $lead->leadscaptainId,
            'leadscaptain_public_identifier' => $lead->leadscaptainPublicIdentifier,
            'leadscaptain_member_id' => $lead->leadscaptainMemberId,

            'first_name' => $lead->firstName,
            'last_name' => $lead->lastName,
            'gender' => $lead->gender,
            'country_code' => $lead->countryCode,
            'persona' => $lead->persona,
            'summary' => $lead->summary,
            'birth_year' => $lead->birthYear,

            'position_title' => $lead->positionTitle,
            'position_location' => $lead->positionLocation,
            'position_description' => $lead->positionDescription,
            'position_started_at' => $lead->positionStartedAt,

            'company_name' => $lead->companyName,
            'company_leadscaptain_id' => $lead->companyLeadscaptainId,
            'company_leadscaptain_universal_name' => $lead->companyLeadscaptainUniversalName,
            'company_salesforce_id' => $lead->companySalesforceId,
            'company_spendesk_id' => $lead->companySpendeskId,
            'company_hubspot_id' => $lead->companyHubspotId,
            'industry_name' => $lead->industryName,

            'linkedin_url' => $lead->linkedinUrl,
            'linkedin_source' => $lead->linkedinSource,
            'sales_navigator_source' => $lead->salesNavigatorSource,
            'email_status' => $lead->emailStatus,
            'email_last_checked' => $lead->emailLastChecked,

            'emails' => json_encode($lead->emails, JSON_THROW_ON_ERROR),
            'personal_emails' => json_encode($lead->personalEmails, JSON_THROW_ON_ERROR),
            'phones' => json_encode($lead->phones, JSON_THROW_ON_ERROR),
            'marvin_searches' => json_encode($lead->marvinSearches, JSON_THROW_ON_ERROR),
            'skills' => json_encode($lead->skills, JSON_THROW_ON_ERROR),
            'languages' => json_encode($lead->languages, JSON_THROW_ON_ERROR),
            'schools' => json_encode($lead->schools, JSON_THROW_ON_ERROR),
            'external_searches' => json_encode($lead->externalSearches, JSON_THROW_ON_ERROR),

            'created_at' => $now,
            'updated_at' => $now,
        ];
    }
}
<?php

namespace App\Application\Lead;

use App\Domain\Lead\DTOs\LeadData;

final class LeadDataMapper
{
    public function map(array $lead): LeadData
    {
       
        return new LeadData(
            leadscaptainId: $lead['leadscaptain_id'],
            leadscaptainPublicIdentifier: $lead['leadscaptain_public_identifier'] ?? null,
            leadscaptainMemberId: isset($lead['leadscaptain_member_id'])
                ? (string) $lead['leadscaptain_member_id']
                : null,
        
            firstName: $lead['first_name'] ?? null,
            lastName: $lead['last_name'] ?? null,
            gender: $lead['gender'] ?? null,
            countryCode: $lead['country_code'] ?? null,
            persona: $lead['persona'] ?? null,
            summary: $lead['summary'] ?? null,
            birthYear: isset($lead['birth_year'])
                ? (string) $lead['birth_year']
                : null,
        
            positionTitle: $lead['position_title'] ?? null,
            positionLocation: $lead['position_location'] ?? null,
            positionDescription: $lead['position_description'] ?? null,
            positionStartedAt: $lead['position_started_at'] ?? null,
        
            companyName: $lead['company_name'] ?? null,
            companyLeadscaptainId: $lead['company_leadscaptain_id'] ?? null,
            companyLeadscaptainUniversalName: $lead['company_leadscaptain_universal_name'] ?? null,
            companySalesforceId: $lead['company_salesforce_id'] ?? null,
            companySpendeskId: $lead['company_spendesk_id'] ?? null,
            companyHubspotId: $lead['company_hubspot_id'] ?? null,
            industryName: $lead['industry_name'] ?? null,
        
            linkedinUrl: $lead['linkedin_url'] ?? null,
            linkedinSource: (bool) ($lead['linkedin_source'] ?? false),
            salesNavigatorSource: (bool) ($lead['sales_navigator_source'] ?? false),
            emailStatus: $lead['email_status'] ?? null,
            emailLastChecked: $lead['email_last_checked'] ?? null,
            emails: is_array($lead['emails'] ?? null)
         ? $lead['emails']
    : [],
    personalEmails: is_array($lead['personal_emails'] ?? null)
    ? $lead['personal_emails']
    : [],
    phones: is_array($lead['phones'] ?? null)
    ? $lead['phones']
    : [],
    marvinSearches: is_array($lead['marvin_searches'] ?? null)
    ? $lead['marvin_searches']
    : [],
    skills: is_array($lead['skills'] ?? null)
    ? $lead['skills']
    : [],
    languages: is_array($lead['languages'] ?? null)
    ? $lead['languages']
    : [],
    schools: is_array($lead['schools'] ?? null)
    ? $lead['schools']
    : [],
    externalSearches: is_array($lead['external_searches'] ?? null)
    ? $lead['external_searches']
    : [],
        );

        

        
    }
}
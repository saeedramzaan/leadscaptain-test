<?php

namespace Tests\Feature;

use App\Application\Lead\LeadDataMapper;
use Tests\TestCase;

class LeadDataMapperTest extends TestCase
{
    public function test_it_maps_api_lead_to_lead_data(): void
    {
        $lead = [
            'leadscaptain_id' => 'lead-123',
            'leadscaptain_public_identifier' => 'public-123',
            'leadscaptain_member_id' => 123,

            'first_name' => 'John',
            'last_name' => 'Doe',
            'gender' => 'male',
            'country_code' => 'US',
            'persona' => 'Technical',
            'summary' => 'Test lead',
            'birth_year' => '1990',

            'position_title' => 'Engineering Manager',
            'position_location' => 'New York',
            'position_description' => 'Manages engineering teams',
            'position_started_at' => '2024-01-01',

            'company_name' => 'Example Inc',
            'company_leadscaptain_id' => 'company-123',
            'company_leadscaptain_universal_name' => 'example-inc',
            'company_salesforce_id' => null,
            'company_spendesk_id' => null,
            'company_hubspot_id' => null,
            'industry_name' => 'Technology',

            'linkedin_url' => 'https://linkedin.com/in/johndoe',
            'linkedin_source' => true,
            'sales_navigator_source' => false,
            'email_status' => 'verified',
            'email_last_checked' => '2026-09-17',

            'emails' => ['john@example.com'],
            'personal_emails' => [],
            'phones' => ['+123456789'],
            'marvin_searches' => [],
            'skills' => ['PHP', 'Laravel'],
            'languages' => ['English'],
            'schools' => [],
            'external_searches' => [],
        ];

        $mapper = app(LeadDataMapper::class);

        $result = $mapper->map($lead);

        $this->assertSame('lead-123', $result->leadscaptainId);
        $this->assertSame('John', $result->firstName);
        $this->assertSame('Doe', $result->lastName);
        $this->assertSame('Example Inc', $result->companyName);
        $this->assertSame('Engineering Manager', $result->positionTitle);
        $this->assertTrue($result->linkedinSource);
        $this->assertSame(['john@example.com'], $result->emails);
        $this->assertSame(['+123456789'], $result->phones);
        $this->assertSame(['PHP', 'Laravel'], $result->skills);
    }
}
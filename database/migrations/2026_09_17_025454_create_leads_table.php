<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();

            // Leadscaptain identifiers
            $table->string('leadscaptain_id')->unique();
            $table->string('leadscaptain_public_identifier')->nullable();
            $table->string('leadscaptain_member_id')->nullable();

            // Person
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('gender')->nullable();
            $table->string('country_code', 10)->nullable();
            $table->string('persona')->nullable();
            $table->text('summary')->nullable();
            $table->string('birth_year')->nullable();

            // Position
            $table->string('position_title')->nullable();
            $table->string('position_location')->nullable();
            $table->text('position_description')->nullable();
            $table->timestamp('position_started_at')->nullable();

            // Company
            $table->string('company_name')->nullable();
            $table->string('company_leadscaptain_id')->nullable();
            $table->string('company_leadscaptain_universal_name')->nullable();
            $table->string('company_salesforce_id')->nullable();
            $table->string('company_spendesk_id')->nullable();
            $table->string('company_hubspot_id')->nullable();
            $table->string('industry_name')->nullable();

            // Contact / social
            $table->string('linkedin_url')->nullable();
            $table->boolean('linkedin_source')->default(false);
            $table->boolean('sales_navigator_source')->default(false);
            $table->string('email_status')->nullable();
            $table->timestamp('email_last_checked')->nullable();

            // Complex Leadscaptain fields
            $table->jsonb('emails')->nullable();
            $table->jsonb('personal_emails')->nullable();
            $table->jsonb('phones')->nullable();
            $table->jsonb('marvin_searches')->nullable();
            $table->jsonb('skills')->nullable();
            $table->jsonb('languages')->nullable();
            $table->jsonb('schools')->nullable();
            $table->jsonb('external_searches')->nullable();

            $table->timestamps();

            // Useful indexes for common lookups
            $table->index('company_name');
            $table->index('country_code');
            $table->index('email_status');
            $table->index('position_title');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
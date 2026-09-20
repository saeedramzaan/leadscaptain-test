# Leadscaptain Lead Importer

A production-oriented Laravel application for importing leads from the Leadscaptain API, processing paginated responses, and persisting lead data efficiently and idempotently.

The project is designed as a technical interview implementation with an emphasis on reliability, testability, maintainability, background processing, retry handling, and Docker-based development.

## Tech Stack

* PHP 8.4
* Laravel 12
* PostgreSQL 17
* Docker / Docker Compose
* Composer
* PHPUnit / Laravel testing tools
* GitHub Actions

## Architecture

The application follows an Onion Architecture / DDD-inspired structure, separating business concerns from infrastructure details.

```text
                    ┌──────────────────────────┐
                    │       Application        │
                    │                          │
                    │      ImportLeads         │
                    └────────────┬─────────────┘
                                 │
                    ┌────────────▼─────────────┐
                    │          Domain          │
                    │                          │
                    │ LeadData / Repository    │
                    │        contract          │
                    └────────────┬─────────────┘
                                 │
                    ┌────────────▼─────────────┐
                    │      Infrastructure      │
                    │                          │
                    │ LeadscaptainClient       │
                    │ EloquentLeadRepository   │
                    └────────────┬─────────────┘
                                 │
                  ┌──────────────┴──────────────┐
                  │                             │
            Leadscaptain API               PostgreSQL
```

### Main layers

#### Domain

Contains business-level contracts and data structures that should not depend on external infrastructure.

* `app/Domain/Lead/DTOs/LeadData.php`
* `app/Domain/Lead/Repositories/LeadRepository.php`

#### Application

Coordinates the lead import use case.

* `app/Application/Lead/ImportLeads.php`
* `app/Application/Lead/LeadDataMapper.php`

The application layer coordinates API retrieval, mapping, and persistence without directly depending on Eloquent implementation details.

#### Infrastructure

Contains external system integrations.

* `app/Infrastructure/Leadscaptain/LeadscaptainClient.php`
* `app/Infrastructure/Lead/EloquentLeadRepository.php`

The Leadscaptain client is responsible for HTTP communication, while the repository is responsible for database persistence.

#### Background Jobs

* `app/Jobs/ImportAllLeadsJob.php`
* `app/Jobs/ImportLeadsPageJob.php`

`ImportAllLeadsJob` discovers the number of API pages and dispatches individual page jobs.

Each `ImportLeadsPageJob` processes one API page independently.

This allows multiple workers to process independent pages concurrently.

---

## Import Flow

```text
ImportAllLeadsJob
       │
       │ Request page 1
       ▼
Leadscaptain API
       │
       │ total_pages
       ▼
Dispatch page jobs
       │
       ├──────────────┐
       ▼              ▼
 Page 1 Job       Page 2 Job      ... Page N Job
       │              │                 │
       ▼              ▼                 ▼
Leadscaptain     Leadscaptain       Leadscaptain
       │              │                 │
       └──────────────┴─────────────────┘
                      │
                      ▼
                 Map LeadData
                      │
                      ▼
                 Bulk Upsert
                      │
                      ▼
                  PostgreSQL
```

Pages are independent once pagination metadata has been discovered, making them suitable for bounded concurrent processing through multiple queue workers.

---

## Pagination

The Leadscaptain API exposes paginated results.

The importer:

1. Requests the first page.
2. Reads `total_pages` from the response.
3. Dispatches one background job per page.
4. Each page job requests its assigned page.
5. The returned leads are mapped to `LeadData`.
6. Leads are persisted using a bulk upsert.

The API's documented `limit` parameter defaults to `100`. The implementation does not assume that `100` is the API's maximum because the API contract does not document a maximum value.

---

## Idempotency and Duplicate Handling

Leadscaptain's lead identifier is stored as a unique database key:

```text
leadscaptain_id
```

Persistence uses Laravel's bulk `upsert()` operation.

This means the importer can safely process the same lead more than once:

```text
Existing lead
     │
     │ same leadscaptain_id
     ▼
   UPDATE

New lead
     │
     │ new leadscaptain_id
     ▼
   INSERT
```

This is important for:

* retries
* repeated imports
* duplicate API results
* concurrent page processing

Repeated processing therefore does not create duplicate lead records.

---

## API Reliability

The Leadscaptain HTTP client uses Laravel's HTTP client retry mechanism.

Retries are configured for:

* HTTP `429 Too Many Requests`
* HTTP `5xx Server Errors`
* connection failures

HTTP `401 Unauthorized` is not retried because repeating an authentication failure does not normally resolve the problem.

Example retry configuration:

```env
LEADSCAPTAIN_RETRY_TIMES=5
LEADSCAPTAIN_RETRY_SLEEP=200
```

`LEADSCAPTAIN_RETRY_TIMES=5` means the initial request can be followed by up to five retries.

The HTTP client also has a configurable timeout:

```env
LEADSCAPTAIN_TIMEOUT=10
```

Unexpected or unsuccessful API responses are converted into application exceptions so queue workers can handle them according to the job retry policy.

---

## Queue Processing

The application uses Laravel's database queue.

Queue configuration:

```env
QUEUE_CONNECTION=database
```

The queue uses two important tables:

```text
jobs
    │
    └── pending queue work

failed_jobs
    │
    └── jobs that exhausted their queue attempts
```

The page import job has:

```php
public int $tries = 3;
public int $backoff = 30;
```

Therefore a failed page job can be attempted again by the queue worker.

The Docker Compose configuration runs multiple worker replicas, allowing independent page jobs to be processed concurrently.

Example:

```bash
docker-compose up -d --scale worker=3
```

This provides bounded concurrency rather than creating an unbounded number of simultaneous API requests.

---

## Docker

The project provides a PHP 8.4 Docker environment and PostgreSQL 17.

Start the application:

```bash
docker-compose up -d --scale worker=3
```

Check running containers:

```bash
docker-compose ps
```

The main application container is:

```text
leadscaptain-app
```

The PostgreSQL container is:

```text
leadscaptain-postgres
```

Three queue workers can be started with:

```bash
docker-compose up -d --scale worker=3
```

---

## Installation

Clone the repository:

```bash
git clone git@github.com:saeedramzaan/leadscaptain-test.git
cd leadscaptain-test
```

Copy the environment file:

```bash
cp .env.example .env
```

Generate the Laravel application key:

```bash
docker-compose run --rm app php artisan key:generate
```

Start the application:

```bash
docker-compose up -d --scale worker=3
```

Run migrations:

```bash
docker-compose exec app php artisan migrate
```

Install dependencies if required:

```bash
docker-compose exec app composer install
```

---

## Configuration

Leadscaptain configuration is externalized through environment variables.

```env
LEADSCAPTAIN_API_URL=https://api.leadscaptain.com
LEADSCAPTAIN_API_TOKEN=
LEADSCAPTAIN_TIMEOUT=10
LEADSCAPTAIN_RETRY_TIMES=5
LEADSCAPTAIN_RETRY_SLEEP=200
```

The API token must be supplied locally and must **not** be committed to Git.

The repository intentionally contains an empty token in `.env.example`.

Database configuration for Docker:

```env
DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=leadscaptain
DB_USERNAME=leadscaptain
DB_PASSWORD=leadscaptain_secret
```

---

## Running Tests

Run the complete test suite:

```bash
docker exec leadscaptain-app php artisan test
```

The test suite covers:

* successful API requests
* API server errors
* HTTP retry behavior
* rate limiting (`429`)
* authentication failures (`401`)
* connection failures
* exhausted HTTP retries
* lead mapping
* lead persistence
* bulk upsert behavior
* pagination
* page jobs
* import orchestration
* queue retry configuration
* permanently failed queue jobs

Current test suite:

```text
22 tests passed
64 assertions
```

Most external API behavior is tested using Laravel HTTP fakes, so an API key is not required to run the automated tests.

---

## Testing Queue Failures

Queue failures are tested separately from normal application exceptions.

A deterministic test-only job is used to verify that a permanently failing job eventually moves from:

```text
jobs
  │
  │ retry
  ▼
jobs
  │
  │ retry
  ▼
jobs
  │
  │ final failure
  ▼
failed_jobs
```

This verifies the Laravel queue failure lifecycle without depending on a real external API failure.

---

## CI/CD

GitHub Actions runs automatically on pushes and pull requests.

The CI pipeline:

1. Checks out the repository.
2. Installs PHP 8.4.
3. Installs required PHP extensions.
4. Installs Composer dependencies.
5. Creates the Laravel environment.
6. Generates the application key.
7. Starts PostgreSQL 17.
8. Runs database migrations.
9. Executes the automated test suite.

Workflow:

```text
Git Push / Pull Request
          │
          ▼
    GitHub Actions
          │
          ├── PHP 8.4
          ├── PostgreSQL 17
          ├── Composer
          ├── Migrations
          └── PHPUnit
                │
                ▼
             CI Result
```

The CI workflow is located at:

```text
.github/workflows/ci.yml
```

---

## API Authentication

The Leadscaptain API token is intentionally not included in the repository.

For local development, set:

```env
LEADSCAPTAIN_API_TOKEN=your-token-here
```

The token should be provided through the local `.env` file or another secret-management mechanism in a production environment.

Never commit credentials to Git.

---

## Real API Verification

The automated test suite uses mocked API responses and therefore does not require a real Leadscaptain API key.

Once the API key is provided, the same application service and queue flow can be used against the real API.

The real API verification should confirm:

* authentication
* response structure
* pagination
* lead mapping
* persistence
* duplicate handling
* retry behavior for temporary failures

The API token should remain local and should not be added to GitHub.

---

## Project Structure

```text
app/
├── Application/
│   └── Lead/
│       ├── ImportLeads.php
│       └── LeadDataMapper.php
│
├── Domain/
│   └── Lead/
│       ├── DTOs/
│       │   └── LeadData.php
│       └── Repositories/
│           └── LeadRepository.php
│
├── Infrastructure/
│   ├── Lead/
│   │   └── EloquentLeadRepository.php
│   └── Leadscaptain/
│       └── LeadscaptainClient.php
│
├── Jobs/
│   ├── ImportAllLeadsJob.php
│   └── ImportLeadsPageJob.php
│
└── Models/
    └── Lead.php

database/
└── migrations/
    └── create_leads_table.php

tests/
├── Feature/
└── Unit/

docker/
└── php/
    └── Dockerfile

.github/
└── workflows/
    └── ci.yml
```

---

## Design Decisions

### Bulk upsert instead of individual inserts

Leads are persisted using bulk `upsert()` operations.

This reduces the number of database operations when processing large pages and makes repeated processing idempotent.

### Page-level queue jobs

Each API page is represented by an independent queue job.

This provides:

* retry isolation
* bounded concurrency
* better failure recovery
* easier horizontal scaling

A failure on one page does not require restarting the entire import.

### Dependency inversion

The application layer depends on the `LeadRepository` interface rather than directly depending on the Eloquent implementation.

The concrete implementation is registered in Laravel's service container.

This makes the application easier to test and allows the persistence mechanism to change without changing the import use case.

### Externalized configuration

API URLs, tokens, timeout values, and retry settings are provided through environment configuration rather than hard-coded application logic.

### Mocked external API tests

HTTP fakes are used for automated tests so the test suite remains deterministic and does not depend on external network availability or API credentials.

---

## Security

* API credentials are stored in `.env`.
* `.env` is excluded from Git.
* `.env.example` contains no real credentials.
* Private SSH keys are never stored in the repository.
* External API authentication is performed through request headers.

---

## Current Status

Implemented:

* [x] Laravel 12 application
* [x] PHP 8.4 Docker environment
* [x] PostgreSQL 17
* [x] Leadscaptain API client
* [x] Configurable API timeout
* [x] HTTP retry handling
* [x] API error handling
* [x] Lead DTO
* [x] Lead mapping
* [x] Repository abstraction
* [x] PostgreSQL persistence
* [x] Bulk upsert / idempotent persistence
* [x] API pagination
* [x] Queue-based page processing
* [x] Queue retry configuration
* [x] Failed queue job handling
* [x] Multiple queue workers
* [x] Automated tests
* [x] Docker Compose
* [x] GitHub Actions CI
* [x] GitHub repository

Pending final verification:

* [ ] Real Leadscaptain API verification using the provided API key
* [ ] Final production-readiness review

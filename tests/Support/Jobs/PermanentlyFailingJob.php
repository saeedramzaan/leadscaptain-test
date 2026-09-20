<?php

namespace Tests\Support\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class PermanentlyFailingJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 0;

    public function handle(): void
    {
        throw new \RuntimeException('Permanent test failure.');
    }
}
<?php

namespace App\Domain;

use Drutogoti\CaptureContext;
use Illuminate\Database\Events\QueryExecuted;

/** Uses the owner's capture container, never its raw SQL/binding/cookie watchers. */
final class RequestDiagnostics
{
    private CaptureContext $capture;

    public function __construct()
    {
        $this->capture = new CaptureContext;
    }

    public function query(QueryExecuted $query): void
    {
        $this->capture->addQuery(['fingerprint' => hash('sha256', $query->sql), 'duration_ms' => $query->time, 'connection' => $query->connectionName]);
    }

    public function summary(): array
    {
        $counts = array_count_values(array_column($this->capture->queries, 'fingerprint'));

        return ['queries' => count($this->capture->queries), 'query_ms' => round(array_sum(array_column($this->capture->queries, 'duration_ms')), 2), 'repeated_fingerprints' => count(array_filter($counts, fn ($n) => $n >= 3)), 'peak_memory_mb' => round(memory_get_peak_usage(true) / 1048576, 2)];
    }
}

<?php

namespace App\Domain;

use App\Models\AuditLog;

final class Audit
{
    public static function record(string $action, string $subject, array $metadata = []): void
    {
        AuditLog::create(['user_id' => auth()->id(), 'action' => $action, 'subject' => $subject, 'metadata' => $metadata]);
    }
}

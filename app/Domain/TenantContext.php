<?php

namespace App\Domain;

use App\Models\Tenant;

final class TenantContext
{
    private ?Tenant $tenant = null;

    public function set(Tenant $tenant): void
    {
        if (! array_key_exists('commission_bps', $tenant->getAttributes())) {
            $tenant->refresh();
        }
        $this->tenant = $tenant;
    }

    public function get(): Tenant
    {
        return $this->tenant ?? throw new \LogicException('Tenant context is required.');
    }

    public function id(): int
    {
        return $this->get()->id;
    }

    public function clear(): void
    {
        $this->tenant = null;
    }
}

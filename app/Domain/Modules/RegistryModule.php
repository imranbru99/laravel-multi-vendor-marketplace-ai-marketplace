<?php

namespace App\Domain\Modules;

use App\Domain\ModuleManager;

final readonly class RegistryModule implements Module
{
    public function __construct(private string $identifier, private array $definition) {}

    public function key(): string
    {
        return $this->identifier;
    }

    public function name(): string
    {
        return $this->definition['name'];
    }

    public function dependencies(): array
    {
        return $this->definition['dependencies'];
    }

    public function metadata(): array
    {
        return $this->definition + ['version' => '1.0.0', 'scope' => 'platform / tenant / plan / role'.(in_array($this->identifier, app(ModuleManager::class)->scopedKeys(), true) ? ' / vendor / branch' : ''), 'installed' => true, 'configuration_schema' => [], 'conflicts' => [], 'permission' => 'manage-marketplace'];
    }
}

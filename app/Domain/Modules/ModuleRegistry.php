<?php

namespace App\Domain\Modules;

final class ModuleRegistry
{
    public function all(): array
    {
        $result = [];
        foreach (config('marketplace.modules') as $key => $definition) {
            $result[$key] = new RegistryModule($key, $definition);
        } $this->validate($result);

        return $result;
    }

    public function find(string $key): Module
    {
        return $this->all()[$key] ?? throw new \InvalidArgumentException('Unknown module.');
    }

    public function validate(array $modules): void
    {
        $visited = [];
        $visiting = [];
        $visit = function (string $key) use (&$visit, &$visited, &$visiting, $modules) {
            if (isset($visited[$key])) {
                return;
            }if (isset($visiting[$key])) {
                throw new \LogicException('Cyclic module dependency.');
            }if (! isset($modules[$key])) {
                throw new \LogicException('Missing module dependency '.$key);
            } $visiting[$key] = true;
            foreach ($modules[$key]->dependencies() as $dependency) {
                $visit($dependency);
            }unset($visiting[$key]);
            $visited[$key] = true;
        };
        foreach (array_keys($modules) as $key) {
            $visit($key);
        }
    }
}

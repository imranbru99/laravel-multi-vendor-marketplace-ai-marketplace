<?php

namespace App\Domain\Modules;

interface Module
{
    public function key(): string;

    public function name(): string;

    public function dependencies(): array;

    public function metadata(): array;
}

<?php

namespace Mpietrucha\Storage\Contracts;

use Illuminate\Support\Collection;
use Mpietrucha\Storage\Contracts\ProcessorInterface;

interface AdapterInterface
{
    public function disableExpiry(): self;

    public function processor(): ProcessorInterface;

    public function delete(): void;

    public function get(): Collection;

    public function set(Collection $storage): void;
}

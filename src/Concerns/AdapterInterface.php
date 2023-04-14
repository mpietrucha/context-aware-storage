<?php

namespace Mpietrucha\Storage\Concerns;

use Illuminate\Support\Collection;

interface AdapterInterface
{
    public function delete(): void;

    public function get(): Collection;

    public function set(Collection $storage): void;
}

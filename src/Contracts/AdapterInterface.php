<?php

namespace Mpietrucha\Storage\Contracts;

use Illuminate\Support\Collection;

interface AdapterInterface
{
    public function delete(): void;

    public function get(): Collection;

    public function set(Collection $storage): void;
}

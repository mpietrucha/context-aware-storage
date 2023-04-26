<?php

namespace Mpietrucha\Storage\Contracts;

use Illuminate\Support\Enumerable;
use Mpietrucha\Storage\Contracts\ProcessorInterface;

interface AdapterInterface
{
    public function table(?string $table): ?string;

    public function processor(): ProcessorInterface;

    public function delete(): void;

    public function get(): Enumerable;

    public function set(Enumerable $storage): void;
}

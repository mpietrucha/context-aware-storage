<?php

namespace Mpietrucha\Storage\Contracts;

use Closure;
use Mpietrucha\Storage\Contracts\AdapterInterface;

interface ProcessorInterface
{
    public function __construct(AdapterInterface $adapter);

    public function get(?string $key = null): mixed;

    public function raw(?string $key = null): mixed;

    public function put(string $key, mixed $value): void;

    public function append(string $key, mixed $value): void;

    public function appendUnique(string $key, mixed $value, Closure $callback): void;

    public function exists(string $key): bool;

    public function existsUnique(string $key, Closure $callback, mixed $value = null): bool;

    public function forget(string $key): void;

    public function forgetIndex(string $key, int $index): void;
}

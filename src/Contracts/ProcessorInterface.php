<?php

namespace Mpietrucha\Storage\Contracts;

use Closure;
use Mpietrucha\Support\Serializer;
use Illuminate\Support\Collection;
use Mpietrucha\Storage\Contracts\ExpiryInterface;
use Mpietrucha\Storage\Contracts\AdapterInterface;

interface ProcessorInterface
{
    public function __construct(AdapterInterface $adapter, ?ExpiryInterface $expiry);

    public function serialized(?string $key = null, ?Closure $callback = null): null|string|Collection;

    public function serializer(?string $key = null): null|Serializer|Collection;

    public function get(?string $key = null): mixed;

    public function put(string $key, mixed $value, mixed $expires = null): void;

    public function append(string $key, mixed $value, mixed $expires = null): void;

    public function appendUnique(string $key, mixed $value, Closure $callback, mixed $expires = null): void;

    public function exists(string $key): bool;

    public function existsUnique(string $key, Closure $callback, mixed $value = null): bool;

    public function forget(string $key): void;

    public function forgetIndex(string $key, int $index): void;
}

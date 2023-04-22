<?php

namespace Mpietrucha\Storage\Contracts;

use Closure;

interface ExpiryInterface
{
    public function expiry(string $key, mixed $expires): void;

    public function expired(?string $key, Closure $callback): void;
}

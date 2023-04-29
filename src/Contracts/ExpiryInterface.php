<?php

namespace Mpietrucha\Storage\Contracts;

use Closure;

interface ExpiryInterface extends TableInterface
{
    public function onExpiresResolved(Closure $callback): void;

    public function expiry(string $key, mixed $expires): void;

    public function expired(?string $key, Closure $callback): void;
}

<?php

namespace Mpietrucha\Storage\Contracts;

use Closure;
use Mpietrucha\Storage\Contracts\ExpiryDateResolverInterface;

interface ExpiryInterface extends TableInterface
{
    public function resolver(mixed $expires): ExpiryDateResolverInterface;

    public function expiry(string $key, mixed $expires): void;

    public function expired(?string $key, Closure $callback): void;

    public function onExpiresResolved(Closure $callback): void;

    public function overrideOnExists(bool $mode = true): void;
}

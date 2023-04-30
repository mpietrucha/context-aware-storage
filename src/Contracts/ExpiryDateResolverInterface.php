<?php

namespace Mpietrucha\Storage\Contracts;

use Closure;

interface ExpiryDateResolverInterface
{
    public function encode(?Closure $resolver = null): int;

    public function expired(): bool;
}

<?php

namespace Mpietrucha\Storage\Contracts;

interface TransformerInterface
{
    public function transform(?string $table, ?string $key): ?string;

    public function is(?string $table, ?string $key): bool;
}

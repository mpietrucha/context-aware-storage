<?php

namespace Mpietrucha\Storage\Contracts;

interface TransformerInterface extends TableInterface
{
    public function transform(?string $key): ?string;

    public function is(?string $key): bool;
}

<?php

namespace Mpietrucha\Storage\Contracts;

interface TransformerInterface
{
    public function table(string $table): void;

    public function shouldTransform(): bool;

    public function inside(?string $key): bool;

    public function transform(?string $key): ?string;
}

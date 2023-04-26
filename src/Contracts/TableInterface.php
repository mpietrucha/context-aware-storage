<?php

namespace Mpietrucha\Storage\Contracts;

interface TableInterface
{
    public function table(?string $table): void;
}

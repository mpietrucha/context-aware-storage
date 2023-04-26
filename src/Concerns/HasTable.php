<?php

namespace Mpietrucha\Storage\Concerns;

trait HasTable
{
    protected ?string $table = null;

    public function table(?string $table): void
    {
        $this->table = $table;
    }
}

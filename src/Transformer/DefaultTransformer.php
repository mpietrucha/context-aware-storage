<?php

namespace Mpietrucha\Storage\Transformer;

use Mpietrucha\Support\Hash;
use Illuminate\Support\Collection;

class DefaultTransformer extends AbstractTransformer
{
    public function build(?string $key): ?Collection
    {
        if (! $key) {
            return null;
        }

        return collect($key)->when($this->table, fn (Collection $builder) => $builder->prepend(
            Hash::md5($this->table)
        ));
    }
}

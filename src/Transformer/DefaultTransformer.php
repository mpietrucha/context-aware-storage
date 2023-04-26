<?php

namespace Mpietrucha\Storage\Transformer;

use Mpietrucha\Support\Hash;
use Illuminate\Support\Collection;
use Mpietrucha\Storage\Factory\Transformer;

class DefaultTransformer extends Transformer
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

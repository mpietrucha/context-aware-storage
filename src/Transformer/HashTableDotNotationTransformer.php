<?php

namespace Mpietrucha\Storage\Transformer;

use Mpietrucha\Support\Hash;
use Illuminate\Support\Collection;
use Mpietrucha\Storage\Factory\Transformer;

class HashTableDotNotationTransformer extends Transformer
{
    protected function build(?string $table, ?string $key): ?Collection
    {
        if (! $key) {
            return null;
        }

        if (! $table) {
            return collect($key);
        }

        return collect([Hash::md5($table), $key]);
    }
}

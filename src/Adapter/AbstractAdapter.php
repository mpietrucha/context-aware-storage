<?php

namespace Mpietrucha\Storage\Adapter;

use Mpietrucha\Support\Concerns\HasFactory;
use Mpietrucha\Storage\Contracts\AdapterInterface;

abstract class AbstractAdapter implements AdapterInterface
{
    use HasFactory;
}

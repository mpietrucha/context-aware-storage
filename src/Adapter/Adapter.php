<?php

namespace Mpietrucha\Storage\Adapter;

use Mpietrucha\Support\Concerns\HasFactory;
use Mpietrucha\Storage\Contracts\AdapterInterface;

abstract class Adapter implements AdapterInterface
{
    use HasFactory;
}

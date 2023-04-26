<?php

namespace Mpietrucha\Storage\Factory;

use Mpietrucha\Support\Concerns\HasFactory;
use Mpietrucha\Storage\Contracts\AdapterInterface;

abstract class Adapter implements AdapterInterface
{
    use HasFactory;
}

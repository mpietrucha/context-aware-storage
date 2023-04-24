<?php

namespace Mpietrucha\Storage\Expiry;

use Mpietrucha\Support\Hash;
use Mpietrucha\Storage\Adapter;
use Mpietrucha\Storage\Factory\Expiry;
use Mpietrucha\Support\Concerns\HasVendor;
use Mpietrucha\Storage\Adapter\FileAdapter;

class FileExpiry extends Expiry
{
    use HasVendor;

    protected const FILE = 'internal_expiry_manager';

    protected function adapter(): Adapter
    {
        $adapter = FileAdapter::create()->file(
            Hash::md5($this->vendor(), self::FILE)
        );

        return Adapter::create($adapter)->expiry(null);
    }
}

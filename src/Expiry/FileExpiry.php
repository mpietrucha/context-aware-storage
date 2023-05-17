<?php

namespace Mpietrucha\Storage\Expiry;

use Mpietrucha\Storage\Adapter;

class FileExpiry extends AbstractExpiry
{
    protected const FILE = 'internal_expiry_manager';

    protected ?Adapter $adapter = null;

    protected function adapter(): Adapter
    {
        return $this->adapter ??= Adapter::create()->table(self::FILE, $this->table)->stale();
    }
}

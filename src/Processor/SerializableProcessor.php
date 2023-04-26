<?php

namespace Mpietrucha\Storage\Processor;

use Mpietrucha\Support\Serializer;
use Mpietrucha\Storage\Factory\Processor;

class SerializableProcessor extends Processor
{
    public function get(?string $key = null): mixed
    {
        return $this->entries($key, fn (?string $entry) => Serializer::create($entry)->unserialize());
    }

    public function put(string $key, mixed $value): void
    {
        $storage = $this->adapter->get()->merge([
            $key => Serializer::create($value)->serialize()
        ]);

        $this->adapter->set($storage);
    }
}

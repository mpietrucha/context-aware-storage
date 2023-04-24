<?php

namespace Mpietrucha\Storage\Processor;

use Closure;
use Mpietrucha\Support\Condition;
use Mpietrucha\Support\Serializer;
use Illuminate\Support\Collection;
use Mpietrucha\Storage\Factory\Processor;
use Mpietrucha\Storage\Contracts\AdapterInterface;

class SerializableProcessor extends Processor
{
    public function __construct(protected AdapterInterface $adapter)
    {
    }

    public function get(?string $key = null): mixed
    {
        return $this->entries($key, fn (?string $entry) => Serializer::create($entry)->unserialize());
    }

    public function raw(?string $key = null): mixed
    {
        return $this->entries($key);
    }

    public function put(string $key, mixed $value): void
    {
        $storage = $this->adapter->get()->put($key, Serializer::create($value)->serialize());

        $this->adapter->set($storage);
    }

    protected function entries(?string $key, ?Closure $callback = null): mixed
    {
        $entry = Condition::create($storage = $this->adapter->get())->add(fn () => $storage->get($key), $key)->resolve();

        if (! $callback) {
            return $entry;
        }

        if ($entry instanceof Collection) {
            return $entry->mapRecursive($callback);
        }

        return $callback($entry);
    }
}

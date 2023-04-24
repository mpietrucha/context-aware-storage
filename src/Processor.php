<?php

namespace Mpietrucha\Storage;

use Exception;
use Closure;
use Mpietrucha\Support\Serializer;
use Mpietrucha\Support\Caller;
use Mpietrucha\Support\Pipeline;
use Mpietrucha\Support\Condition;
use Illuminate\Support\Collection;
use Mpietrucha\Storage\Contracts\ExpiryInterface;
use Mpietrucha\Storage\Contracts\AdapterInterface;
use Mpietrucha\Storage\Contracts\ProcessorInterface;

class Processor implements ProcessorInterface
{
    public function __construct(protected AdapterInterface $adapter, protected ?ExpiryInterface $expiry)
    {
    }

    public function serialized(?string $key, Closure ...$callbacks): mixed
    {
        $this->expiry?->expired($key, $this->forget(...));

        $entry = Condition::create($storage = $this->adapter->get())->add(fn () => $storage->get($key), $key)->resolve();

        $callback = fn (mixed $entry) => Pipeline::create()->send($entry)->through($callbacks)->thenReturn();

        if ($entry instanceof Collection) {
            return $entry->mapRecursive($callback);
        }

        return $callback($entry);
    }

    public function serializer(?string $key, Closure ...$callbacks): mixed
    {
        return $this->serialized(function (mixed $entry, Closure $next) {
            return $next(Serializer::create($entry));
        }, ...$callbacks);
    }

    public function get(?string $key, Closure ...$callbacks): mixed
    {
        return $this->serializer($key, function (Serializer $serializer, Closure $next) {
            return $next($serializer->unserialize());
        }, ...$callbacks);
    }

    public function put(string $key, mixed $value, mixed $expires = null): void
    {
        $this->expiry?->expiry($key, $expires);

        $storage = $this->adapter->get()->put($key, Serializer::create($value)->serialize());

        $this->adapter->set($storage);
    }

    public function append(string $key, mixed $value, mixed $expires = null): void
    {
        $current = $this->enshureCollection($key, true);

        $this->put($key, $current->push($value), $expires);
    }

    public function appendUnique(string $key, mixed $value, Closure $callback, mixed $expires = null): void
    {
        if ($this->existsUnique($key, $callback, $value)) {
            return;
        }

        $this->append($key, $value, $expires);
    }

    public function exists(string $key): bool
    {
        return $this->adapter->get()->has($key);
    }

    public function existsUnique(string $key, Closure $callback, mixed $value = null): bool
    {
        if (! $this->exists($key)) {
            return false;
        }

        $current = $this->enshureCollection($key, true);

        $this->put($temporaryKey = str()->uuid(), $value);

        $exists = $current->first(fn (mixed $entry) => $callback($entry, $temporaryValueEntry ??= $this->get($temporaryKey))) !== null;

        $this->forget($temporaryKey);

        return $exists;
    }

    public function forget(string $key): void
    {
        $storage = $this->adapter->get()->forget($key);

        $this->adapter->set($storage);

        if ($this->adapter->get()->count()) {
            return;
        }

        $this->adapter->delete();
    }

    public function forgetIndex(string $key, int $index): void
    {
        $current = $this->enshureCollection($key);

        $current->splice($index, 1);

        $this->put($key, $current);

        if ($this->get($key)->count()) {
            return;
        }

        $this->forget($key);
    }

    protected function enshureCollection(string $key, bool $default = false): Collection
    {
        $current = $this->get($key);

        if ($default) {
            $current ??= collect();
        }

        if (! $current instanceof Collection) {
            throw new Exception("Cannot append to key `$key` with previously non array value.");
        }

        return $current;
    }
}

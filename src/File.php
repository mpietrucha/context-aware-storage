<?php

namespace Mpietrucha\Storage;

use Exception;
use Mpietrucha\Support\Hash;
use Mpietrucha\Support\Json;
use Mpietrucha\Support\Macro;
use Mpietrucha\Support\Types;
use Mpietrucha\Support\Condition;
use Illuminate\Support\Collection;
use Mpietrucha\Support\Concerns\HasVendor;
use Mpietrucha\Support\Concerns\HasFactory;

class File
{
    use HasVendor;
    use HasFactory;

    protected ?string $path = null;

    protected ?Collection $storage = null;

    public function __construct(protected ?string $prefix = null, protected ?string $directory = null, protected ?string $file = null)
    {
        Macro::bootstrap();
    }

    public function __unserialize(array $data): void
    {
        $this->flush();
    }

    public function prefix(string $prefix): self
    {
        $this->prefix = Hash::md5($prefix);

        return $this;
    }

    public function directory(string $directory): self
    {
        $this->directory = $directory;

        return $this->flush();
    }

    public function file(string $file): self
    {
        $this->file = $file;

        return $this->flush();
    }

    public function temporary(): self
    {
        return $this->directory(sys_get_temp_dir());
    }

    public function shared(): self
    {
        return $this->file(Hash::md5($this->vendor()));
    }

    public function delete(): void
    {
        unlink($this->path());

        $this->flush();
    }

    public function put(string $key, mixed $value): void
    {
        $this->storage()->put($this->key($key), Entry::create($value)->value());

        $this->persist();
    }

    public function append(string $key, mixed $value): void
    {
        $current = $this->get($key) ?? collect();

        if (Types::string($current)) {
            throw new Exception("Cannot append to key $key with previously string value.");
        }

        $this->put($key, $current->push($value)->unique());

        $this->persist();
    }

    public function forget(string $key): void
    {
        $this->storage()->forget($this->key($key));

        $this->persist();
    }

    public function forgetIndex(string $key, int $index): void
    {
        $current = $this->get($key);

        if (Types::string($current)) {
            return;
        }

        $current->splice($index, 1);

        $this->put($key, $current);

        if ($this->get($key)->count()) {
            return;
        }

        $this->forget($key);
    }

    public function get(?string $key = null): mixed
    {
        $storage = $this->storage()->mapRecursive(fn (string $entry) => Entry::create($entry)->resolve());

        if (! $key) {
            return $storage->recursive();
        }

        $current = $storage->get($this->key($key));

        if ($current instanceof Collection) {
            return $current->recursive();
        }

        return $current;
    }

    protected function storage(): Collection
    {
        if ($this->storage) {
            return $this->storage;
        }

        $this->storage = Json::decodeToCollection(
            file_get_contents($this->path())
        );

        return $this->storage;
    }

    protected function key(string $key): string
    {
        return collect([$this->prefix, $key])->filter()->toDotWord();
    }

    protected function persist(): void
    {
        file_put_contents($this->path(), Json::encode($this->storage()));
    }

    protected function path(): string
    {
        if (! $this->directory || ! $this->file) {
            throw new Exception('Directory or file has no value.');
        }

        if (! $this->path) {
            $this->path = collect([$this->directory, $this->file])->toDirectory();

            touch($this->path);
        }

        return $this->path;
    }

    protected function flush(): self
    {
        $this->storage = $this->path = null;

        return $this;
    }
}

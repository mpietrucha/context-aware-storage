<?php

namespace Mpietrucha\Storage\Adapter;

use Mpietrucha\Support\Hash;
use Mpietrucha\Support\Json;
use Mpietrucha\Support\Macro;
use Mpietrucha\Storage\Expiry;
use Mpietrucha\Storage\Processor;
use Illuminate\Support\Collection;
use Mpietrucha\Support\Concerns\HasVendor;
use Mpietrucha\Support\Concerns\HasFactory;
use Mpietrucha\Storage\Contracts\AdapterInterface;
use Mpietrucha\Storage\Contracts\ProcessorInterface;

class File implements AdapterInterface
{
    use HasVendor;

    use HasFactory;

    protected string $file;

    protected string $directory;

    public function __construct(protected ?Expiry $expiry = new Expiry)
    {
        Macro::bootstrap();

        $this->directory(sys_get_temp_dir())->file(
            Hash::md5($this->vendor())
        );
    }

    public function directory(string $directory): self
    {
        $this->directory = $directory;

        return $this;
    }

    public function disableExpiry(): self
    {
        $this->expiry = null;

        return $this;
    }

    public function processor(): ProcessorInterface
    {
        return new Processor($this, $this->expiry);
    }

    public function file(string $file): self
    {
        $this->file = $file;

        return $this;
    }

    public function delete(): void
    {
        if (! $this->exists()) {
            return;
        }

        unlink($this->path());
    }

    public function get(): Collection
    {
        if (! $this->exists()) {
            return collect();
        }

        return Json::decodeToCollection(
            file_get_contents($this->path())
        );
    }

    public function set(Collection $storage): void
    {
        file_put_contents($this->path(), Json::encode($storage));
    }

    protected function path(): string
    {
        return collect([$this->directory, $this->file])->toDirectory();
    }

    protected function exists(): bool
    {
        return file_exists($this->path());
    }
}

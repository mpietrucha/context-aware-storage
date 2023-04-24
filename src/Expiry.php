<?php

namespace Mpietrucha\Storage;

use Closure;
use DateTime;
use Carbon\Carbon;
use Mpietrucha\Support\Hash;
use Mpietrucha\Support\Types;
use Mpietrucha\Storage\Adapter\File;
use Mpietrucha\Support\Concerns\HasVendor;
use Mpietrucha\Support\Concerns\HasFactory;
use Mpietrucha\Storage\Contracts\ExpiryInterface;
use Mpietrucha\Storage\Contracts\AdapterInterface;

class Expiry implements ExpiryInterface
{
    use HasVendor;

    protected const FILE = 'internal_expiry_manager';

    public function expiry(string $key, mixed $expires): void
    {
        if (! $expires) {
            return;
        }

        if ($expires instanceof DateTime) {
            $this->adapter()->put($key, $expires->getTimestamp());

            return;
        }

        if (! Types::array($expires)) {
            $expires = [$expires, 'minutes'];
        }

        $this->adapter()->put($key, Carbon::now()->add(...$expires)->getTimestamp());
    }

    public function expired(?string $key, Closure $callback): void
    {
        if (! $key) {
            return;
        }

        if (! $expiry = $this->adapter()->get($key)) {
            return;
        }

        if (Carbon::createFromTimestamp($expiry)->isBefore(Carbon::now())) {
            return;
        }

        $callback($key);
    }

    protected function adapter(): Adapter
    {
        $adapter = File::create()->disableExpiry()->file(
            Hash::md5($this->vendor(), self::FILE)
        );

        return Adapter::create($adapter);
    }
}

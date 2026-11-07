<?php

declare(strict_types=1);

namespace YouVision\ThinkUuid\facade;

use DateTimeInterface;
use Ramsey\Uuid\UuidInterface;
use think\Facade;

/**
 * @method static UuidInterface v1(?string $node = null, ?int $clockSeq = null)
 * @method static UuidInterface v3(string|UuidInterface|null $namespace, string $name)
 * @method static UuidInterface v4()
 * @method static UuidInterface v5(string|UuidInterface|null $namespace, string $name)
 * @method static UuidInterface v6(?string $node = null, ?int $clockSeq = null)
 * @method static UuidInterface v7(?DateTimeInterface $time = null)
 * @method static UuidInterface v8(string $bytes)
 * @method static UuidInterface make(?string $version = null, mixed ...$arguments)
 * @method static UuidInterface|string generate(?string $version = null, mixed ...$arguments)
 * @method static UuidInterface parse(UuidInterface|string $value)
 * @method static bool isValid(mixed $value)
 * @method static UuidInterface namespace(string|UuidInterface|null $namespace = null)
 * @method static \Ramsey\Uuid\UuidFactoryInterface getFactory()
 * @method static array getConfig()
 *
 * @see \YouVision\ThinkUuid\Uuid
 */
class Uuid extends Facade
{
    protected static function getFacadeClass(): string
    {
        return 'uuid';
    }
}
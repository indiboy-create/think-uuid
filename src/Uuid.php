<?php

declare(strict_types=1);

namespace YouVision\ThinkUuid;

use DateTimeInterface;
use InvalidArgumentException;
use Ramsey\Uuid\Type\Hexadecimal;
use Ramsey\Uuid\Uuid as RamseyUuid;
use Ramsey\Uuid\UuidFactory;
use Ramsey\Uuid\UuidFactoryInterface;
use Ramsey\Uuid\UuidInterface;

class Uuid
{
    public const VERSIONS = ['1', '3', '4', '5', '6', '7', '8'];

    public const NAMESPACES = [
        'dns' => RamseyUuid::NAMESPACE_DNS,
        'url' => RamseyUuid::NAMESPACE_URL,
        'oid' => RamseyUuid::NAMESPACE_OID,
        'x500' => RamseyUuid::NAMESPACE_X500,
    ];

    protected array $config;

    protected UuidFactoryInterface $factory;

    public function __construct(array $config = [])
    {
        $this->config = $config;
        $this->factory = new UuidFactory();
    }

    public function v1(?string $node = null, ?int $clockSeq = null): UuidInterface
    {
        return $this->factory->uuid1(
            $this->nodeHex($node ?? $this->configString('node')) ?: null,
            $clockSeq ?? $this->configInt('clock_seq')
        );
    }

    public function v3(string|UuidInterface|null $namespace, string $name): UuidInterface
    {
        return $this->factory->uuid3($this->namespace($namespace), $name);
    }

    public function v4(): UuidInterface
    {
        return $this->factory->uuid4();
    }

    public function v5(string|UuidInterface|null $namespace, string $name): UuidInterface
    {
        return $this->factory->uuid5($this->namespace($namespace), $name);
    }

    public function v6(?string $node = null, ?int $clockSeq = null): UuidInterface
    {
        $node = $this->nodeHex($node ?? $this->configString('node'));

        return $this->factory->uuid6(
            $node ? new Hexadecimal($node) : null,
            $clockSeq ?? $this->configInt('clock_seq')
        );
    }

    public function v7(?DateTimeInterface $time = null): UuidInterface
    {
        return $this->factory->uuid7($time);
    }

    public function v8(string $bytes): UuidInterface
    {
        if (strlen($bytes) === 32 && ctype_xdigit($bytes)) {
            $bytes = hex2bin($bytes);
        }

        if (strlen($bytes) !== 16) {
            throw new InvalidArgumentException('v8 的字节内容必须为 16 字节二进制数据或 32 位十六进制字符串');
        }

        return $this->factory->uuid8($bytes);
    }

    public function make(?string $version = null, mixed ...$arguments): UuidInterface
    {
        $version = $this->normalizeVersion($version);
        $method = 'v' . $version;

        return $this->{$method}(...$arguments);
    }

    public function generate(?string $version = null, mixed ...$arguments): UuidInterface|string
    {
        $uuid = $this->make($version, ...$arguments);

        return match (strtolower($this->configString('return_type', 'object'))) {
            'string' => $uuid->toString(),
            'simple' => $uuid->getHex()->toString(),
            default  => $uuid,
        };
    }

    public function parse(UuidInterface|string $value): UuidInterface
    {
        if ($value instanceof UuidInterface) {
            return $value;
        }

        return RamseyUuid::fromString($value);
    }

    public function isValid(mixed $value): bool
    {
        if ($value instanceof UuidInterface) {
            return true;
        }

        return is_string($value) && RamseyUuid::isValid($value);
    }

    public function namespace(string|UuidInterface|null $namespace = null): UuidInterface
    {
        if ($namespace instanceof UuidInterface) {
            return $namespace;
        }

        $namespace = $namespace ?: $this->configString('namespace', 'dns');
        $key = strtolower($namespace);

        if (isset(self::NAMESPACES[$key])) {
            return RamseyUuid::fromString(self::NAMESPACES[$key]);
        }

        if (!RamseyUuid::isValid($namespace)) {
            throw new InvalidArgumentException(sprintf('无效的 UUID 命名空间：%s', $namespace));
        }

        return RamseyUuid::fromString($namespace);
    }

    public function getFactory(): UuidFactoryInterface
    {
        return $this->factory;
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    protected function normalizeVersion(?string $version): string
    {
        $version = strtolower(trim((string) ($version ?: $this->configString('version', 'v4'))));
        $version = ltrim($version, 'v');

        if (!in_array($version, self::VERSIONS, true)) {
            throw new InvalidArgumentException(sprintf(
                '不支持的 UUID 版本：%s，仅支持 v1、v3、v4、v5、v6、v7、v8',
                $version
            ));
        }

        return $version;
    }

    protected function nodeHex(string $node): string
    {
        $node = preg_replace('/[:\-\s]/', '', $node) ?? '';

        if ($node === '') {
            return '';
        }

        if (!ctype_xdigit($node) || strlen($node) > 12) {
            throw new InvalidArgumentException(sprintf('无效的节点标识：%s', $node));
        }

        return str_pad(strtolower($node), 12, '0', STR_PAD_LEFT);
    }

    protected function configString(string $key, string $default = ''): string
    {
        $value = $this->config[$key] ?? $default;

        return is_scalar($value) ? (string) $value : $default;
    }

    protected function configInt(string $key): ?int
    {
        $value = $this->config[$key] ?? null;

        return $value === null || $value === '' ? null : (int) $value;
    }
}
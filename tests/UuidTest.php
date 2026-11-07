<?php

declare(strict_types=1);

namespace YouVision\ThinkUuid\Tests;

use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid as RamseyUuid;
use Ramsey\Uuid\UuidInterface;
use YouVision\ThinkUuid\Uuid;

class UuidTest extends TestCase
{
    protected Uuid $uuid;

    protected function setUp(): void
    {
        $this->uuid = new Uuid(require dirname(__DIR__) . '/config/uuid.php');
    }

    public function testV4ReturnsValidRandomUuid(): void
    {
        $uuid = $this->uuid->v4();

        $this->assertInstanceOf(UuidInterface::class, $uuid);
        $this->assertSame(4, $uuid->getVersion());
        $this->assertNotSame($uuid->toString(), $this->uuid->v4()->toString());
    }

    public function testV3UsesConfiguredDnsNamespace(): void
    {
        $uuid = $this->uuid->v3(null, 'example.com');

        $this->assertSame(3, $uuid->getVersion());
        $this->assertSame(
            RamseyUuid::uuid3(RamseyUuid::fromString(RamseyUuid::NAMESPACE_DNS), 'example.com')->toString(),
            $uuid->toString()
        );
        $this->assertSame($uuid->toString(), $this->uuid->v3(null, 'example.com')->toString());
    }

    public function testV5UsesConfiguredDnsNamespace(): void
    {
        $uuid = $this->uuid->v5(null, 'example.com');

        $this->assertSame(5, $uuid->getVersion());
        $this->assertSame(
            RamseyUuid::uuid5(RamseyUuid::fromString(RamseyUuid::NAMESPACE_DNS), 'example.com')->toString(),
            $uuid->toString()
        );
    }

    public function testV3AndV5AcceptCustomNamespace(): void
    {
        $namespace = '6ba7b810-9dad-11d1-80b4-00c04fd430c8';

        $this->assertSame(
            RamseyUuid::uuid5(RamseyUuid::fromString($namespace), 'foo')->toString(),
            $this->uuid->v5($namespace, 'foo')->toString()
        );
    }

    public function testV1UsesConfiguredNode(): void
    {
        $uuid = (new Uuid(['node' => '00:11:22:33:44:55']))->v1();

        $this->assertSame(1, $uuid->getVersion());
        $this->assertSame('001122334455', $uuid->getNodeHex());
    }

    public function testV6UsesConfiguredNode(): void
    {
        $uuid = (new Uuid(['node' => '001122334455']))->v6();

        $this->assertSame(6, $uuid->getVersion());
        $this->assertSame('001122334455', $uuid->getNodeHex());
    }

    public function testInvalidNodeIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new Uuid(['node' => 'zz:11:22:33:44:55']))->v1();
    }

    public function testV7AcceptsDateTime(): void
    {
        $time = new DateTimeImmutable('2026-01-02 03:04:05');
        $uuid = $this->uuid->v7($time);

        $this->assertSame(7, $uuid->getVersion());
        $this->assertSame(7, $this->uuid->v7()->getVersion());
    }

    public function testV8AcceptsHexAndBinaryBytes(): void
    {
        $hex = '00112233445566778899aabbccddeeff';

        $fromHex = $this->uuid->v8($hex);
        $fromBinary = $this->uuid->v8(hex2bin($hex));

        $this->assertSame(8, $fromHex->getVersion());
        $this->assertSame($fromHex->toString(), $fromBinary->toString());
        $this->assertSame($fromHex->toString(), $this->uuid->v8(strtoupper($hex))->toString());
    }

    public function testV8RejectsInvalidLength(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->uuid->v8('abcd');
    }

    public function testMakeUsesConfiguredDefaultVersion(): void
    {
        $uuid = (new Uuid(['version' => 'v5']))->make(null, null, 'example.com');

        $this->assertSame(5, $uuid->getVersion());
    }

    public function testMakeRejectsUnsupportedVersion(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->uuid->make('v9');
    }

    public function testNamespaceRejectsInvalidValue(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->uuid->v5('not-a-uuid', 'foo');
    }

    public function testGenerateRespectsReturnType(): void
    {
        $object = (new Uuid(['return_type' => 'object']))->generate('v4');
        $string = (new Uuid(['return_type' => 'string']))->generate('v4');
        $simple = (new Uuid(['return_type' => 'simple']))->generate('v4');

        $this->assertInstanceOf(UuidInterface::class, $object);
        $this->assertIsString($string);
        $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $string);
        $this->assertMatchesRegularExpression('/^[0-9a-f]{32}$/', $simple);
    }

    public function testParseAndIsValid(): void
    {
        $uuid = $this->uuid->v4();

        $this->assertTrue($this->uuid->isValid($uuid));
        $this->assertTrue($this->uuid->isValid($uuid->toString()));
        $this->assertFalse($this->uuid->isValid('not-a-uuid'));
        $this->assertSame($uuid->toString(), $this->uuid->parse($uuid->toString())->toString());
    }

    public function testGetFactoryAndConfig(): void
    {
        $this->assertSame($this->uuid->getFactory(), $this->uuid->getFactory());
        $this->assertSame('v4', $this->uuid->getConfig()['version']);
    }
}
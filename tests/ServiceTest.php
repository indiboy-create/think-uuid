<?php

declare(strict_types=1);

namespace YouVision\ThinkUuid\Tests;

use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\UuidInterface;
use think\App;
use YouVision\ThinkUuid\Service;
use YouVision\ThinkUuid\Uuid;

class ServiceTest extends TestCase
{
    protected function createApp(array $userConfig = []): App
    {
        $app = new App();

        if ($userConfig !== []) {
            $app->config->set($userConfig, 'uuid');
        }

        $app->register(Service::class);

        return $app;
    }

    public function testServiceBindsUuidInstance(): void
    {
        $app = $this->createApp();

        $uuid = $app->make('uuid');

        $this->assertInstanceOf(Uuid::class, $uuid);
        $this->assertSame('v4', $uuid->getConfig()['version']);
    }

    public function testUserConfigOverridesPackageDefault(): void
    {
        $app = $this->createApp(['version' => 'v7', 'namespace' => 'url']);

        $uuid = $app->make('uuid');

        $this->assertSame('v7', $uuid->getConfig()['version']);
        $this->assertSame('url', $uuid->getConfig()['namespace']);
        $this->assertSame(7, $uuid->generate()->getVersion());
    }

    public function testHelperReturnsString(): void
    {
        $this->createApp(['return_type' => 'string']);

        $value = uuid('v4');

        $this->assertIsString($value);
        $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $value);
    }

    public function testHelperUsesConfiguredNamespace(): void
    {
        $this->createApp(['namespace' => 'url']);

        $this->assertSame(
            \Ramsey\Uuid\Uuid::uuid5(\Ramsey\Uuid\Uuid::fromString(\Ramsey\Uuid\Uuid::NAMESPACE_URL), 'foo')->toString(),
            uuid('v5', null, 'foo')
        );
    }

    public function testFacadeResolvesFromContainer(): void
    {
        $this->createApp();

        $uuid = \YouVision\ThinkUuid\facade\Uuid::v4();

        $this->assertInstanceOf(UuidInterface::class, $uuid);
        $this->assertSame(4, $uuid->getVersion());
        $this->assertTrue(\YouVision\ThinkUuid\facade\Uuid::isValid($uuid));
    }
}